<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate;

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Deduplicator\DeduplicatorInterface;
use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDelay;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Reference\Currency\Type\Currency;
use BaksDev\Reference\Money\Type\Money;
use BaksDev\Yandex\Market\Products\Api\Products\Price\GetYaMarketProductPriceRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Price\UpdateYaMarketProductPriceRequest;
use BaksDev\Yandex\Market\Products\Api\Tariffs\YandexMarketCalculatorRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use BaksDev\Yandex\Market\Repository\YaMarketTokensByProfile\YaMarketTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Обновляем базовую цену товара на Yandex Market
 */
#[AsMessageHandler]
final readonly class YaMarketProductsPriceUpdate
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private YandexMarketCalculatorRequest $marketCalculatorRequest,
        private UpdateYaMarketProductPriceRequest $marketProductPriceRequest,
        private CurrentYaMarketProductCardInterface $marketProductsCard,
        private MessageDispatchInterface $messageDispatch,
        private YaMarketTokensByProfileInterface $YaMarketTokensByProfile,
        private GetYaMarketProductPriceRequest $GetYaMarketProductPriceRequest,
    ) {}


    public function __invoke(YaMarketProductsPriceMessage $message): void
    {

        /** Получаем все токены профиля */

        $tokensByProfile = $this->YaMarketTokensByProfile->findAll($message->getProfile());

        if(false === $tokensByProfile || false === $tokensByProfile->valid())
        {
            return;
        }

        $CurrentYaMarketProductCardResult = $this->marketProductsCard
            ->forProduct($message->getProduct())
            ->forOfferConst($message->getOfferConst())
            ->forVariationConst($message->getVariationConst())
            ->forModificationConst($message->getModificationConst())
            ->forProfile($message->getProfile())
            ->find();

        if(false === ($CurrentYaMarketProductCardResult instanceof CurrentYaMarketProductCardResult))
        {
            return;
        }

        /** Не обновляем базовую стоимость карточки без обязательных параметров */
        if(false === $CurrentYaMarketProductCardResult->isCredentials())
        {
            return;
        }

        foreach($tokensByProfile as $YaMarketTokenUid)
        {
            /**
             * Добавляем к стоимости товара стоимость услуг YaMarket
             * Стоимости товара + Стоимость услуг YaMarket + Торговая наценка
             */

            $Price = $this->marketCalculatorRequest
                ->forTokenIdentifier($YaMarketTokenUid)
                ->category($CurrentYaMarketProductCardResult->getMarketCategory())
                ->price($CurrentYaMarketProductCardResult->getProductPrice())
                ->width($CurrentYaMarketProductCardResult->getWidth())
                ->height($CurrentYaMarketProductCardResult->getHeight())
                ->length($CurrentYaMarketProductCardResult->getLength())
                ->weight($CurrentYaMarketProductCardResult->getWeight())
                ->calc();

            if(false === $Price)
            {
                if($CurrentYaMarketProductCardResult->getProductQuantity() > 0)
                {
                    $this->messageDispatch->dispatch(
                        message: $message,
                        stamps: [new MessageDelay('1 minutes')],
                        transport: $message->getProfile().'-low',
                    );

                    $this->logger->critical(
                        sprintf(
                            'yandex-market-products: Пробуем обновит стоимость %s через 1 минуту',
                            $CurrentYaMarketProductCardResult->getArticle(),
                        ),
                        [self::class.':'.__LINE__, $YaMarketTokenUid],
                    );
                }

                continue;
            }

            /**
             * Проверяем изменение цены в карточке если добавлена ранее
             */

            $MarketProductPrice = $this->GetYaMarketProductPriceRequest
                ->forTokenIdentifier($YaMarketTokenUid)
                ->article($CurrentYaMarketProductCardResult->getArticle())
                ->find();

            /** Не обновляем, если стоимость в карточке не изменилась */
            if($Price->getRoundValue() === $MarketProductPrice)
            {
                continue;
            }

            $update = $this->marketProductPriceRequest
                ->forTokenIdentifier($YaMarketTokenUid)
                ->article($CurrentYaMarketProductCardResult->getArticle())
                ->currency($CurrentYaMarketProductCardResult->getProductCurrency())
                ->price($Price)
                ->update();

            if(false === $update)
            {
                $this->messageDispatch->dispatch(
                    message: $message,
                    stamps: [new MessageDelay('1 minute')],
                    transport: $message->getProfile().'-low',
                );

                $this->logger->critical(sprintf(
                    '%s: Пробуем обновить стоимость продукта через 1 минуту',
                    $CurrentYaMarketProductCardResult->getArticle(),
                ));

                continue;
            }

            $this->logger->info(
                sprintf(
                    'Обновили стоимость товара %s (%s) : %s => %s',
                    $CurrentYaMarketProductCardResult->getArticle(),
                    $CurrentYaMarketProductCardResult->getProductPrice(), // стоимость в карточке
                    $MarketProductPrice, // предыдущая стоимость на маркетплейс
                    $Price->getRoundValue(), // новая стоимость на маркетплейс
                ), [self::class.':'.__LINE__, (string) $YaMarketTokenUid],
            );
        }

    }
}
