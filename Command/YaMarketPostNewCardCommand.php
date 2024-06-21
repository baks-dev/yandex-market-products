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

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Entity\Card\YaMarketProductsCard;
use BaksDev\Yandex\Market\Products\Repository\Card\ProductsNotExistsYaMarketCard\ProductsNotExistsYaMarketCardInterface;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\Market\YaMarketProductsCardMarketDTO;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\YaMarketProductsCardDTO;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\YaMarketProductsCardHandler;
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
    name: 'baks:yandex-market-products:post:new',
    description: 'Выгружает новые карточки на Yandex Market'
)
]
class YaMarketPostNewCardCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly ProductsNotExistsYaMarketCardInterface $productsNotExistsYaMarketCard,
        private readonly YaMarketProductsCardHandler $marketProductsCardHandler,
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
        /** Получаем все новые карточки, которых нет в маркете */
        $YaMarketProductsCardMarket = $this->productsNotExistsYaMarketCard->findAll($profile);

        /** @var YaMarketProductsCardMarketDTO $YaMarketProductsCardMarketDTO */
        foreach($YaMarketProductsCardMarket as $i => $YaMarketProductsCardMarketDTO)
        {
            $YaMarketProductsCardMarketDTO->setProfile($profile);

            $YaMarketProductsCardDTO = new YaMarketProductsCardDTO();
            $YaMarketProductsCardDTO->setMarket($YaMarketProductsCardMarketDTO);

            $YaMarketProductsCard = $this->marketProductsCardHandler->handle($YaMarketProductsCardDTO);

            if($YaMarketProductsCard instanceof YaMarketProductsCard)
            {
                $this->io->text(sprintf('Добавили карточку с артикулом %s', $YaMarketProductsCardMarketDTO->getSku()));
            }
            else
            {
                $this->io->warning(sprintf('%s: Ошибка при добавлении карточки с артикулом %s', $YaMarketProductsCard, $YaMarketProductsCardMarketDTO->getSku()));
            }
        }
    }
}
