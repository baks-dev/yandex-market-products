<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit;

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Counter\YaMarketBarcodeCounterForm;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Modification\YaMarketBarcodeModificationForm;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Name\YaMarketBarcodeNameForm;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Offer\YaMarketBarcodeOfferForm;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\NewEdit\Variation\YaMarketBarcodeVariationForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class YaMarketBarcodeForm extends AbstractType
{
    public function __construct(
        private readonly CategoryChoiceInterface $categoryChoiceRepository,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFieldsRepository,
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('main', ChoiceType::class, [
            'choices' => $this->categoryChoiceRepository->findAll(),
            'choice_value' => function(?CategoryProductUid $category) {
                return $category?->getValue();
            },
            'choice_label' => function(CategoryProductUid $category) {
                return (is_int($category->getAttr()) ? str_repeat(' - ', $category->getAttr() - 1) : '').$category->getOptions();
            },
        ]);

        $builder->add('counter', YaMarketBarcodeCounterForm::class);

        $builder->add('name', YaMarketBarcodeNameForm::class, ['label' => false]);

        $builder->add('offer', YaMarketBarcodeOfferForm::class, ['required' => false]);

        $builder->add('variation', YaMarketBarcodeVariationForm::class, ['required' => false]);

        $builder->add('modification', YaMarketBarcodeModificationForm::class, ['required' => false]);


        $builder->add('property', CollectionType::class, [
            'entry_type' => Property\YaMarketBarcodePropertyForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__property__',
        ]);

        $builder->add('custom', CollectionType::class, [
            'entry_type' => Custom\YaMarketBarcodeCustomForm::class,
            'entry_options' => ['label' => false],
            'label' => false,
            'by_reference' => false,
            'allow_delete' => true,
            'allow_add' => true,
            'prototype_name' => '__custom__',
        ]);

        /* Прототип списка свойств */
        $builder->add('offer_prototype', HiddenType::class, ['mapped' => false, 'disabled' => true]);

        /* Добавить коллекцию ******************************************************/
        $builder->add
        (
            'addProperty',
            ButtonType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-sm btn-outline-primary border-0'], 'disabled' => true]);


        /* Добавить коллекцию ******************************************************/
        $builder->add
        (
            'addCustom',
            ButtonType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-sm btn-outline-primary border-0']]);


        $formModifier = function(FormInterface $form, ?CategoryProductUid $category = null) {
            if($category)
            {
                $choice = $this->propertyFieldsRepository
                    ->category($category)
                    ->getPropertyFieldsCollection();

                $form
                    ->add('offer_prototype', ChoiceType::class, [
                        'choices' => $choice,
                        'choice_value' => function(?CategoryProductSectionFieldUid $type) {
                            return $type?->getValue();
                        },
                        'choice_label' => function(CategoryProductSectionFieldUid $type) {
                            return $type->getAttr();
                        },

                        'label' => false,
                        'expanded' => false,
                        'multiple' => false,
                        'mapped' => false,
                        'attr' => ['style' => 'display: none;'],
                        'disabled' => empty($choice),
                    ]);


                $form->add('property', CollectionType::class, [
                    'entry_type' => Property\YaMarketBarcodePropertyForm::class,
                    'entry_options' => ['label' => false, 'offer_fields' => $choice],
                    'label' => false,
                    'by_reference' => false,
                    'allow_delete' => true,
                    'allow_add' => true,
                    'prototype_name' => '__property__',
                ]);


                /* Добавить коллекцию ******************************************************/
                $form->add
                (
                    'addProperty',
                    ButtonType::class,
                    [
                        'label' => 'Add',
                        'label_html' => true,
                        'attr' => ['class' => 'btn-sm btn-outline-primary border-0'],
                        'disabled' => empty($choice),
                    ]);
            }

        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($formModifier) {
                /** @var YaMarketBarcodeDTO $data */
                $data = $event->getData();
                $builder = $event->getForm();

                if($data->getMain() && $data->isHiddenCategory())
                {

                    $category = $this->categoryChoiceRepository
                        ->category($data->getMain())
                        ->onlyActive()
                        ->find();

                    $builder->add('main', ChoiceType::class, [
                        'choices' => $this->categoryChoiceRepository->findAll(),
                        'choice_value' => function(?CategoryProductUid $category) {
                            return $category?->getValue();
                        },
                        'choice_label' => function(CategoryProductUid $category) {
                            return (is_int($category->getAttr()) ? str_repeat(' - ', $category->getAttr() - 1) : '').$category->getOptions();
                        },
                        'disabled' => true,
                        'label' => $category ? $category->getOptions() : false,
                    ]);

                }

                $formModifier($builder, $data->getMain());
            },
        );

        $builder->get('main')->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($formModifier) {
                $category = $event->getForm()->getData();

                if($category)
                {
                    $formModifier($event->getForm()->getParent(), $category);
                }
            },
        );

        /* Сохранить ******************************************************/
        $builder->add
        (
            'yandex_market_barcode',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults
        (
            [
                'data_class' => YaMarketBarcodeDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ]);
    }
}