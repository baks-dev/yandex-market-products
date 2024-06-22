<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace BaksDev\Yandex\Market\Products\Command;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\ProductYaMarketCard\ProductsYaMarketCardInterface;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Получаем карточки товаров и добавляем отсутствующие
 */
#[AsCommand(
    name: 'baks:yandex-market-products:post:update',
    description: 'Обновляет все карточки на Yandex Market'
)]
class YaMarketPostUpdateCardCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly ProductsYaMarketCardInterface $productsYaMarketCard,
        private readonly MessageDispatchInterface $messageDispatch
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('profile', InputArgument::OPTIONAL, 'Идентификатор профиля');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        $profile = $input->getArgument('profile');

        if(!empty($profile))
        {
            $this->update(new UserProfileUid($profile));
        }
        else
        {
            /** Получаем активные токены авторизации профилей Yandex Market */
            $profiles = $this->allProfileYaMarketToken
                ->onlyActiveToken()
                ->findAll();

            if($profiles->valid())
            {
                /** @var UserProfileUid $profile */
                foreach($profiles as $profile)
                {
                    $this->update($profile);
                }
            }
        }

        $this->io->success('Карточки успешно добавлены в очередь');

        return Command::SUCCESS;
    }

    public function update(UserProfileUid $profile): void
    {
        $this->io->note(sprintf('Обновили профиль %s', $profile));

        /** Получаем все имеющиеся карточки профиля */
        $YaMarketProductsCardMarket = $this->productsYaMarketCard
            ->whereProfile($profile)
            ->findAll();

        foreach($YaMarketProductsCardMarket as $card)
        {
            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                $card['main'],
                $card['event'],
            );

            /** Транспорт async чтобы не мешать очереди профиля */
            $this->messageDispatch->dispatch($YaMarketProductsCardMessage, transport: 'async');

            $this->io->text(sprintf('Обновили артикул %s', $card['sku']));
        }
    }
}
