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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit;

use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\ModificationCategoryProductSectionField\ModificationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\OffersCategoryProductSectionField\OffersCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\VariationCategoryProductSectionField\VariationCategoryProductSectionFieldInterface;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketGetParametersRequest;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketParametersDTO;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyCollection;
use BaksDev\Yandex\Market\Products\Mapper\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters\YaMarketProductsSettingsParametersDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property\YaMarketProductsSettingsPropertyDTO;
use BaksDev\Yandex\Market\Repository\YaMarketTokensByProfile\YaMarketTokensByProfileInterface;
use InvalidArgumentException;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class YaMarketProductsSettingsForm extends AbstractType
{
    public function __construct(
        private readonly OffersCategoryProductSectionFieldInterface $offersCategoryProductSectionField,
        private readonly VariationCategoryProductSectionFieldInterface $variationCategoryProductSectionField,
        private readonly ModificationCategoryProductSectionFieldInterface $modificationCategoryProductSectionField,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly YaMarketProductPropertyCollection $marketProductPropertyCollection,
        private readonly YandexMarketGetParametersRequest $marketParametersRequest,
        private readonly TokenStorageInterface $tokenStorage,
        private readonly YaMarketTokensByProfileInterface $YaMarketTokensByProfileRepository
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            /** @var YaMarketProductsSettingsDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            /** Коллекция свойств категории для выпадающего списка */
            $property_fields = $this->propertyFields
                ->category($data->getSettings())
                ->getPropertyFieldsCollection();

            /**  Добавляем к выбору ТП, варианты и модификации */
            //$offer = $this->propertyFields->getOffersFields($data->getSettings());
            $offer = $this->offersCategoryProductSectionField
                ->category($data->getSettings())
                ->findAllCategoryProductSectionField();

            if($offer)
            {
                array_unshift($property_fields, $offer);

                $variation = $this->variationCategoryProductSectionField
                    ->offer($offer->getValue())
                    ->findAllCategoryProductSectionField();

                if($variation)
                {
                    array_unshift($property_fields, $variation);

                    $modification = $this->modificationCategoryProductSectionField
                        ->variation($variation->getValue())
                        ->findAllCategoryProductSectionField();

                    if($modification)
                    {
                        array_unshift($property_fields, $modification);
                    }
                }
            }

            /**
             * Свойства карточки YaMarket
             * @var YaMarketProductPropertyInterface $case
             */
            foreach($this->marketProductPropertyCollection->casesSettings() as $case)
            {
                $WbProductSettingsPropertyDTO = new YaMarketProductsSettingsPropertyDTO();
                $WbProductSettingsPropertyDTO->setType(new YaMarketProductProperty($case));
                $data->addProperty($WbProductSettingsPropertyDTO);
            }


            /** Получаем список параметров категории маркет */

            $TokenInterface = $this->tokenStorage->getToken();

            $UserProfileUid = $TokenInterface?->getUser()?->getProfile();

            if($UserProfileUid)
            {

                /** Получаем все идентификаторы токенов профиля */
                $profiles = $this->YaMarketTokensByProfileRepository
                    ->findAll($UserProfileUid);

                if(false === $profiles || false === $profiles->valid())
                {
                    throw new InvalidArgumentException('Идентификатор токена профиля не найден');
                }

                $YaMarketTokenUid = $profiles->current();

                $marketParameters = $this->marketParametersRequest
                    ->forTokenIdentifier($YaMarketTokenUid)
                    ->category($data->getMarket())
                    ->findAll();


                /** @var YandexMarketParametersDTO $YandexMarketParametersDTO */

                foreach($marketParameters as $param)
                {
                    if($param->getName() === 'Номер карточки')
                    {
                        continue;
                    }

                    $YaMarketProductsSettingsParametersDTO = new YaMarketProductsSettingsParametersDTO();
                    $YaMarketProductsSettingsParametersDTO->setName($param->getName());
                    $YaMarketProductsSettingsParametersDTO->setType($param->getName());

                    if($param->getValues())
                    {
                        $values = array_column($param->getValues(), 'value');
                        $YaMarketProductsSettingsParametersDTO->setHelp($values);
                    }

                    $data->addParameter($YaMarketProductsSettingsParametersDTO);
                }
            }

            $form->add('property', CollectionType::class, [
                'entry_type' => Property\YaMarketProductsSettingsPropertyForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);


            $form->add('parameters', CollectionType::class, [
                'entry_type' => Parameters\YaMarketProductsSettingsParametersForm::class,
                'entry_options' => ['label' => false, 'property_fields' => $property_fields],
                'label' => false,
                'by_reference' => false,
                'allow_delete' => true,
                'allow_add' => true,
            ]);

        });


        /* Сохранить ******************************************************/
        $builder->add(
            'product_settings',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => YaMarketProductsSettingsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ],);
    }

}
