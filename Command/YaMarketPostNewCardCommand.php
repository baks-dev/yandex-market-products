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
use BaksDev\Yandex\Market\Products\Repository\Card\ProductsNotExistsYaMarketCard\ProductsNotExistsYaMarketCardInterface;

use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\Market\YaMarketProductsCardMarketDTO;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\YaMarketProductsCardDTO;
use BaksDev\Yandex\Market\Products\UseCase\Cards\NewEdit\YaMarketProductsCardHandler;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileTokenInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Получаем карточки товаров и добавляем отсутствующие
 */
#[AsCommand(
    name: 'baks:yandex-market-products:post:new',
    description: 'Выгружает новые карточки на Yandex Market')
]
class YaMarketPostNewCardCommand extends Command
{
    private ProductsNotExistsYaMarketCardInterface $productsNotExistsYaMarketCard;
    private YaMarketProductsCardHandler $marketProductsCardHandler;

    public function __construct(
        ProductsNotExistsYaMarketCardInterface $productsNotExistsYaMarketCard,
        YaMarketProductsCardHandler $marketProductsCardHandler,
    )
    {
        parent::__construct();

        $this->productsNotExistsYaMarketCard = $productsNotExistsYaMarketCard;
        $this->marketProductsCardHandler = $marketProductsCardHandler;
    }

    protected function configure(): void
    {
        $this->addArgument('profile', InputArgument::OPTIONAL, 'Идентификатор профиля');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $profile = $input->getArgument('profile');

        if(!$profile)
        {
            $io->error("Не указан идентификатор профиля пользователя. Пример:".PHP_EOL
                ." php bin/console baks:yandex-market-products:post:new <UID>");
            return Command::INVALID;
        }

        $profile = new UserProfileUid($profile);


        /** Получаем все новые карточки, которых нет в маркете */
        $YaMarketProductsCardMarket = $this->productsNotExistsYaMarketCard->findAll($profile);

        /** @var YaMarketProductsCardMarketDTO $YaMarketProductsCardMarketDTO */
        foreach($YaMarketProductsCardMarket as $i => $YaMarketProductsCardMarketDTO)
        {
            //            if ($i % 600 === 0) {
            //                $io->info($i.': Исключаем блокировку (ОГРАНИЧЕНИЕ! 600 запросов в минуту)');
            //                sleep(60);
            //            }

            // Исключаем блокировку (ОГРАНИЧЕНИЕ! 600 запросов в минуту)'
            usleep(200);

            $YaMarketProductsCardMarketDTO->setProfile($profile);

            /** TODO: */
            dd($YaMarketProductsCardMarketDTO);


            $YaMarketProductsCardDTO = new YaMarketProductsCardDTO();
            $YaMarketProductsCardDTO->setMarket($YaMarketProductsCardMarketDTO);

            $YaMarketProductsCard = $this->marketProductsCardHandler->handle($YaMarketProductsCardDTO);


        }

        dd(46545);


        return Command::SUCCESS;


        if($profile)
        {
            /** Если требуется выбрать профиль из списка */
            if($profile === 'choice')
            {
                $helper = $this->getHelper('question');

                $profiles = $this->allProfileToken->fetchAllWbTokenProfileAssociative();

                $questions = null;

                foreach($profiles as $quest)
                {
                    $questions[] = $quest->getAttr();
                }

                $question = new Question('Профиль пользователя: ');
                $question->setAutocompleterValues($questions);

                $profileName = $helper->ask($input, $output, $question);

                foreach($profiles as $profile)
                {
                    if($profile->getAttr() === $profileName)
                    {
                        break;
                    }
                }
            }

            /* Присваиваем профиль пользователя */
            $profile = new UserProfileUid($profile);

            /* Отправляем сообщение в шину профиля */
            $this->messageDispatch->dispatch(
                message: new WbCardNewMessage($profile),
                transport: (string) $profile,
            );
        }
        else
        {
            foreach($this->allProfileToken->fetchAllWbTokenProfileAssociative() as $profile)
            {
                /* Отправляем сообщение в шину профиля */
                $this->messageDispatch->dispatch(
                    message: new WbCardNewMessage($profile),
                    transport: (string) $profile,
                );
            }
        }

        $io->success('Карточки успешно добавлены в очередь');

        return Command::SUCCESS;
    }

}