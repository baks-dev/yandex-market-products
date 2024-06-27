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
use Symfony\Component\Console\Question\ChoiceQuestion;
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

        /** Получаем активные токены авторизации профилей Yandex Market */
        $profiles = $this->allProfileYaMarketToken
            ->onlyActiveToken()
            ->findAll();

        $profiles = iterator_to_array($profiles);

        $helper = $this->getHelper('question');

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        $question = new ChoiceQuestion(
            'Профиль пользователя',
            // choices can also be PHP objects that implement __toString() method
            $questions,
            0
        );

        $profileName = $helper->ask($input, $output, $question);

        if($profileName === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile);
            }
        }
        else
        {
            $UserProfileUid = null;

            foreach($profiles as $profile)
            {
                if($profile->getAttr() === $profileName)
                {
                    /* Присваиваем профиль пользователя */
                    $UserProfileUid = $profile;
                    break;
                }
            }

            if($UserProfileUid)
            {
                $this->update($UserProfileUid);
            }

        }

        $this->io->success('Карточки успешно обновлены');

        return Command::SUCCESS;
    }

    public function update(UserProfileUid $profile): void
    {
        $this->io->note(sprintf('Обновили профиль %s', $profile->getAttr()));

        /** Получаем все имеющиеся карточки профиля */
        $YaMarketProductsCardMarket = $this->productsYaMarketCard
            ->whereProfile($profile)
            ->findAll();

        foreach($YaMarketProductsCardMarket as $card)
        {
            sleep(1);

            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                $card['main'],
                $card['event'],
            );

            /** Консольную комманду выполняем синхронно */
            $this->messageDispatch->dispatch($YaMarketProductsCardMessage);

            $this->io->text(sprintf('Обновили артикул %s', $card['sku']));
        }
    }
}
