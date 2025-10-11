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

namespace BaksDev\Yandex\Market\Products\Schedule;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Messenger\YaMarketProductsStocksUpdate\YaMarketProductsStocksMessage;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Target;
use Symfony\Component\Scheduler\Attribute\AsCronTask;

/**
 * Обновляем остатки
 *
 * #hourly - в какую-то минуту каждый час
 *
 * @see YaMarketProductsStocksMessage
 * @see https://symfony.com/doc/current/scheduler.html#cron-expression-triggers
 */
#[AsCronTask('#hourly', jitter: 60)] // каждый час
final readonly class UpdateYandexProductStocksCron
{
    public function __construct(
        #[Target('yandexMarketProductsLogger')] private LoggerInterface $logger,
        private MessageDispatchInterface $messageDispatch,
        private AllProfileYaMarketTokenInterface $AllProfileYaMarketTokenRepository,
        private AllProductsIdentifierInterface $AllProductsIdentifierRepository,
    ) {}

    public function __invoke(): void
    {
        /** Получаем все активные профили, у которых активный токен  */
        $profiles = $this->AllProfileYaMarketTokenRepository
            ->onlyActiveToken()
            ->findAll();

        if(false === $profiles || false === $profiles->valid())
        {
            $this->logger->warning(
                'Профили с активными токенами Яндекс не найдены',
                [__FILE__.':'.__LINE__],
            );

            return;
        }

        /** Получаем список */

        /* Получаем все имеющиеся карточки в системе */
        $products = $this->AllProductsIdentifierRepository->findAll();

        if(false === $products || false === $products->valid())
        {
            $this->logger->warning(
                'Карточек для обновления не найдено',
                [__FILE__.':'.__LINE__],
            );

            return;
        }

        $profiles = iterator_to_array($profiles);

        foreach($products as $ProductsIdentifierResult)
        {
            foreach($profiles as $UserProfileUid)
            {
                $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                    $UserProfileUid,
                    $ProductsIdentifierResult->getProductId(),
                    $ProductsIdentifierResult->getProductOfferConst(),
                    $ProductsIdentifierResult->getProductVariationConst(),
                    $ProductsIdentifierResult->getProductModificationConst(),
                );

                $YaMarketProductsStocksMessage = new YaMarketProductsStocksMessage($YaMarketProductsCardMessage);

                $this->messageDispatch->dispatch(
                    message: $YaMarketProductsStocksMessage,
                    transport: $UserProfileUid.'-low',
                );
            }
        }
    }
}