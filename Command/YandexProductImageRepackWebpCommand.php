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

namespace BaksDev\Yandex\Market\Products\Command;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Files\Resources\Messenger\Request\Images\CDNUploadImageMessage;
use BaksDev\Ozon\Products\Entity\Custom\Images\OzonProductCustomImage;
use BaksDev\Yandex\Market\Products\Entity\Custom\Images\YandexMarketProductCustomImage;
use BaksDev\Yandex\Market\Products\Repository\YandexProductImageIdentifierByName\YandexMarketProductImageIdentifierByNameInterface;
use BaksDev\Yandex\Market\Products\Repository\YandexProductImageLocal\YandexMarketProductImageLocalInterface;
use Doctrine\ORM\Mapping\Table;
use FilesystemIterator;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ReflectionAttribute;
use ReflectionClass;
use SplFileInfo;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

#[AsCommand(
    name: 'baks:yandex-market-products:cdn',
    description: 'Комманда отправляет на CDN файлы изображений OzonProductCustomImage'
)]
class YandexProductImageRepackWebpCommand extends Command
{
    public function __construct(
        #[Autowire('%kernel.project_dir%')] private readonly string $upload,
        private readonly MessageDispatchInterface $messageDispatch,
        private readonly YandexMarketProductImageLocalInterface $YandexMarketProductImageLocalRepository,
        private readonly YandexMarketProductImageIdentifierByNameInterface $YandexMarketProductImageIdentifierByNameRepository
    )
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $progressBar = new ProgressBar($output);
        $progressBar->start();

        /**
         * Обрабатываем файлы по базе данных
         */

        $images = $this->YandexMarketProductImageLocalRepository->findAll();

        foreach($images as $image)
        {
            $message = new CDNUploadImageMessage(
                $image->getId(),
                YandexMarketProductCustomImage::class,
                $image->getName(),
            );

            $this->messageDispatch->dispatch(message: $message);

            $progressBar->advance();
        }


        /**
         * Проверяем директории на признак не пережатых файлов
         */


        /** Выделяем из сущности название таблицы для директории файлов */
        $ref = new ReflectionClass(YandexMarketProductCustomImage::class);

        /** @var ReflectionAttribute $current */
        $current = current($ref->getAttributes(Table::class));
        $TABLE = $current->getArguments()['name'] ?? 'images';

        /** Определяем путь к директории файлов */
        $upload = null;
        $upload[] = $this->upload;
        $upload[] = 'public';
        $upload[] = 'upload';
        $upload[] = $TABLE;
        $uploadDir = implode(DIRECTORY_SEPARATOR, $upload);

        if(false === is_dir($uploadDir))
        {
            return Command::SUCCESS;
        }

        $iterator = new RecursiveDirectoryIterator($uploadDir, FilesystemIterator::SKIP_DOTS);

        /** @var SplFileInfo $info */
        foreach(new RecursiveIteratorIterator($iterator) as $info)
        {
            /** Определяем файл в базе данных по названию директории */
            $dirName = basename(dirname($info->getRealPath()));
            $YandexMarketProductImage = $this->YandexMarketProductImageIdentifierByNameRepository->find($dirName);


            if(false === $YandexMarketProductImage)
            {
                $io->warning(sprintf('Изображение OzonProductCustomImage %s не найдено либо уже отправлено на CDN', $dirName));

                unlink($info->getRealPath()); // удаляем файл
                rmdir($info->getPath());  // удаляем пустую директорию

                continue;
            }

            $CDNUploadImageMessage = new CDNUploadImageMessage(
                $YandexMarketProductImage,
                YandexMarketProductCustomImage::class,
                $dirName,
            );

            $this->messageDispatch->dispatch(message: $CDNUploadImageMessage);

            $progressBar->advance();
        }

        /**
         * Завершаем комманду
         */

        $progressBar->finish();

        $io->success('Команда успешно завершена');

        return Command::SUCCESS;
    }
}
