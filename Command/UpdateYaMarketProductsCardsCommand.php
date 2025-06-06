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

namespace BaksDev\Yandex\Market\Products\Command;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Products\Product\Repository\AllProductsIdentifier\AllProductsIdentifierInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Messenger\Card\YaMarketProductsCardMessage;
use BaksDev\Yandex\Market\Products\Repository\Card\CurrentYaMarketProductsCard\CurrentYaMarketProductCardInterface;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Получаем карточки товаров и добавляем отсутствующие
 */
#[AsCommand(
    name: 'baks:yandex-market-products:update:cards',
    description: 'Обновляет все карточки на Yandex Market',
    aliases: ['baks:yandex-products:update:cards', 'baks:yandex:update:cards']
)]
class UpdateYaMarketProductsCardsCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly AllProductsIdentifierInterface $allProductsIdentifier,
        private readonly CurrentYaMarketProductCardInterface $marketProductsCard,
        private readonly MessageDispatchInterface $messageDispatch
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('article', 'a', InputOption::VALUE_OPTIONAL, 'Фильтр по артикулу ((--article=... || -a ...))');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $this->io = new SymfonyStyle($input, $output);

        /** Получаем активные токены авторизации профилей Yandex Market */
        $profiles = $this->allProfileYaMarketToken
            //->onlyActiveToken()
            ->findAll();

        $profiles = iterator_to_array($profiles);

        $helper = $this->getHelper('question');

        $questions[] = 'Все';

        foreach($profiles as $quest)
        {
            $questions[] = $quest->getAttr();
        }

        /** Объявляем вопрос с вариантами ответов */
        $question = new ChoiceQuestion(
            question: 'Профиль пользователя',
            choices: $questions,
            default: 0
        );

        $profileName = $helper->ask($input, $output, $question);

        if($profileName === 'Все')
        {
            /** @var UserProfileUid $profile */
            foreach($profiles as $profile)
            {
                $this->update($profile, $input->getOption('article'));
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
                $this->update($UserProfileUid, $input->getOption('article'));
            }

        }

        $this->io->success('Карточки успешно обновлены');

        return Command::SUCCESS;
    }

    public function update(UserProfileUid $profile, ?string $article = null): void
    {
        $this->io->note(sprintf('Обновляем профиль %s', $profile->getAttr()));

        /* Получаем все имеющиеся карточки в системе */
        $products = $this->allProductsIdentifier->findAllArray();

        if($products === false)
        {
            $this->io->warning('Карточек для обновления не найдено');
            return;
        }

        foreach($products as $product)
        {
            $card = $this->marketProductsCard
                ->forProduct($product['product_id'])
                ->forOfferConst($product['offer_const'])
                ->forVariationConst($product['variation_const'])
                ->forModificationConst($product['modification_const'])
                ->find();

            if($card === false)
            {
                $this->io->warning('Карточки не найдено, либо не указаны настройки соотношений свойств');
                continue;
            }

            /**
             * Если передан артикул - применяем фильтр по вхождению
             */
            if(!empty($article))
            {
                /** Пропускаем обновление, если соответствие не найдено */
                if(stripos($card['article'], $article) === false)
                {
                    $this->io->writeln(sprintf('<fg=gray>... %s</>', $card['article']));

                    continue;
                }
            }

            $YaMarketProductsCardMessage = new YaMarketProductsCardMessage(
                $profile,
                $product['product_id'],
                $product['offer_const'],
                $product['variation_const'],
                $product['modification_const'],
            );

            /** Консольную комманду выполняем синхронно */
            $this->messageDispatch->dispatch($YaMarketProductsCardMessage);

            $this->io->text(sprintf('Обновили артикул %s', $card['article']));

            if($card['article'] === $article)
            {
                break;
            }
        }
    }
}
