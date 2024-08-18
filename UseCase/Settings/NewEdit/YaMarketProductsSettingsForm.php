<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit;

use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\ModificationCategoryProductSectionField\ModificationCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\OffersCategoryProductSectionField\OffersCategoryProductSectionFieldInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;
use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\VariationCategoryProductSectionField\VariationCategoryProductSectionFieldInterface;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketParametersDTO;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketParametersRequest;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyCollection;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters\YaMarketProductsSettingsParametersDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property\YaMarketProductsSettingsPropertyDTO;
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
        private readonly YaMarketProductPropertyCollection $marketProductPropertyCollection,
        private readonly YandexMarketParametersRequest $marketParametersRequest,
        private readonly PropertyFieldsCategoryChoiceInterface $propertyFields,
        private readonly TokenStorageInterface $tokenStorage
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {

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

                /** TODO: */
                //$UserProfileUid = new UserProfileUid('018d464d-c67a-7285-8192-7235b0510924');

                $marketParameters = $this->marketParametersRequest
                    ->profile($UserProfileUid)
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
        ], );
    }

}
