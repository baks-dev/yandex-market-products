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

namespace BaksDev\Yandex\Market\Products\Messenger\Card;

use BaksDev\Core\Lock\AppLockInterface;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YaMarketProductUpdateCardRequest;
use BaksDev\Yandex\Market\Products\Mapper\YandexMarketMapper;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsPriceUpdate\YaMarketProductsPriceMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardResult;
use BaksDev\Yandex\Market\Repository\YaMarketTokensByProfile\YaMarketTokensByProfileInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class YaMarketProductsCardUpdateDispatcher
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private YaMarketProductUpdateCardRequest $marketProductUpdate,
        private CurrentYaMarketProductCardInterface $marketProductsCard,
        private YandexMarketMapper $yandexMarketMapper,
        private YaMarketTokensByProfileInterface $YaMarketTokensByProfile,
        private MessageDispatchInterface $messageDispatch,
    ) {}

    /**
     * Добавляем (обновляем) карточку товара на Yandex Market
     */
    public function __invoke(YaMarketProductsCardMessage $message): void
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
            ->find();

        if(false === ($CurrentYaMarketProductCardResult instanceof CurrentYaMarketProductCardResult))
        {
            return;
        }

        /** Не добавляем карточку без обязательных параметров */
        if(false === $CurrentYaMarketProductCardResult->isCredentials())
        {
            $this->logger->warning(
                sprintf(
                    'yandex-market-products: Не добавляем карточку  c артикулом %s без обязательных параметров (цена, размер)',
                    $CurrentYaMarketProductCardResult->getArticle(),
                ),
                [self::class.':'.__LINE__, var_export($CurrentYaMarketProductCardResult, true)],
            );

            return;
        }

        /** Гидрируем карточку на свойства запроса */
        $request = $this->yandexMarketMapper
            ->getData($CurrentYaMarketProductCardResult);

        foreach($tokensByProfile as $YaMarketTokenUid)
        {
            $update = $this->marketProductUpdate
                ->forTokenIdentifier($YaMarketTokenUid)
                ->update($request);

            if(false === $update)
            {
                /**
                 * Ошибка запишется в лог
                 *
                 * @see YaMarketProductUpdateCardRequest
                 */
                return;
            }

            /** Добавляем в очередь обновление цены  */
            $this->messageDispatch->dispatch(
                message: new YaMarketProductsPriceMessage($message),
                transport: (string) $message->getProfile(),
            );

            /** Добавляем в очередь обновление остатков  */
            $this->messageDispatch->dispatch(
                message: new YaMarketProductsStocksMessage($message),
                transport: (string) $message->getProfile(),
            );

            $this->logger->info(sprintf('Обновили карточку товара %s', $request['offerId']));

        }
    }
}
