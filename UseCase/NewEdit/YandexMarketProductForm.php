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

namespace BaksDev\Yandex\Market\Products\UseCase\NewEdit;

use BaksDev\Core\Twig\TemplateExtension;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\Images\YandexMarketProductImagesForm;
use Exception;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

final class YandexMarketProductForm extends AbstractType
{
    public function __construct(
        private readonly ProductDetailByInvariableInterface $productDetailByInvariable,
        private readonly UserProfileTokenStorageInterface $userProfileTokenStorage,
        private readonly TemplateExtension $templateExtension,
        private readonly Environment $environment,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('images', CollectionType::class, [
            'entry_type' => YandexMarketProductImagesForm::class,
            'entry_options' => [
                'label' => false,
            ],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__images__',
        ]);

        /** Рендеринг шаблона, если описание NULL */
        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event): void {
                /** @var YandexMarketProductDTO $dto */
                $dto = $event->getData();

                $product = $this->productDetailByInvariable
                    ->invariable($dto->getInvariable())
                    ->find()
                    ->current();

                if(null === $product)
                {
                    throw new Exception('Продукт не найден');
                }

                /** Получаем ID текущего профиля пользователя для составления пути для шаблона */
                $userProfile = $this->userProfileTokenStorage->getProfileCurrent();
            }
        );

        /** Сохранить */
        $builder->add(
            'yandex_market_product',
            SubmitType::class,
            [
                'label' => 'Save',
                'label_html' => true,
                'attr' => ['class' => 'btn-primary']
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => YandexMarketProductDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ]);
    }
}