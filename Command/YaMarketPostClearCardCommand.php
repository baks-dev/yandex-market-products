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

use BaksDev\Products\Product\Repository\ProductByArticle\ProductEventByArticleInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\Products\Card\FindProductYandexMarketRequest;
use BaksDev\Yandex\Market\Products\Api\Products\Card\YandexMarketProductDeleteRequest;
use BaksDev\Yandex\Market\Products\Repository\Card\AllProductsTag\AllProductsTagInterface;
use BaksDev\Yandex\Market\Repository\AllProfileToken\AllProfileYaMarketTokenInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * Удаляет отсутствующие карточки
 */
#[AsCommand(
    name: 'baks:yandex-market-products:clear',
    description: 'Удаляет отсутствующие карточки на Yandex Market'
)]
class YaMarketPostClearCardCommand extends Command
{
    private SymfonyStyle $io;

    public function __construct(
        private readonly AllProfileYaMarketTokenInterface $allProfileYaMarketToken,
        private readonly FindProductYandexMarketRequest $findProductYandexMarketRequest,
        private readonly ProductEventByArticleInterface $productEventByArticle,
        private readonly YandexMarketProductDeleteRequest $yandexMarketProductDeleteRequest,
        private readonly AllProductsTagInterface $allProductsTag,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('article', 'a', InputOption::VALUE_OPTIONAL, 'Фильтр по тегам ((--article=... || -a ...))');
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

    public function update(UserProfileUid $profile, ?string $tag = null): void
    {
        $this->io->note(sprintf('Обновляем профиль %s', $profile->getAttr()));

        $tags = $tag ? [['article' => $tag]] : $this->allProductsTag->findAll();

        foreach($tags as $article)
        {
            /** Получаем все карточки YandexMarket по тегу */
            $findProductYandexMarketRequest = $this
                ->findProductYandexMarketRequest
                ->profile($profile)
                ->forTag($article['article']);

            while(true)
            {
                $cards = $findProductYandexMarketRequest->find();

                if($cards === false || $cards->valid() === false)
                {
                    break;
                }

                foreach($cards as $card)
                {
                    $product = $this->productEventByArticle->findProductEventByArticle($card->getArticle());

                    if($product === false)
                    {
                        /** Удаляем на YandexMarket отсутствующую карточку */
                        $this->yandexMarketProductDeleteRequest
                            ->profile($profile)
                            ->delete($card->getArticle());

                        $this->io->text(sprintf('Удаляем артикул %s', $card->getArticle()));
                    }
                }
            }
        }
    }
}
