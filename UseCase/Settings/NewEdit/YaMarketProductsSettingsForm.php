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

use BaksDev\Products\Category\Repository\PropertyFieldsCategoryChoice\PropertyFieldsCategoryChoiceInterface;


use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Products\Category\Type\Offers\Id\CategoryProductOffersUid;
use BaksDev\Products\Category\Type\Section\Field\Id\CategoryProductSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketParametersDTO;
use BaksDev\Yandex\Market\Products\Api\Reference\Parameters\YandexMarketParametersRequest;
use BaksDev\Yandex\Market\Products\Api\Token\Reference\Characteristics\WbCharacteristicByObjectName;
use BaksDev\Yandex\Market\Products\Api\Token\Reference\Characteristics\WbCharacteristicByObjectNameDTO;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyCollection;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\Properties\Collection\YaMarketProductPropertyInterface;
use BaksDev\Yandex\Market\Products\Type\Settings\Property\YaMarketProductProperty;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters\YaMarketProductsSettingsParametersDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property\YaMarketProductsSettingsPropertyDTO;
use BaksDev\Yandex\Market\Repository\WbTokenByProfile\WbTokenByProfileInterface;
use DomainException;
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
    private PropertyFieldsCategoryChoiceInterface $propertyFields;

    //private WbCharacteristicByObjectName $wbCharacteristic;

    //private WbTokenByProfileInterface $wbTokenByProfile;
    private YaMarketProductPropertyCollection $marketProductPropertyCollection;
    private YandexMarketParametersRequest $marketParametersRequest;
    private TokenStorageInterface $tokenStorage;


    public function __construct(
        YaMarketProductPropertyCollection $marketProductPropertyCollection,
        YandexMarketParametersRequest $marketParametersRequest,
        PropertyFieldsCategoryChoiceInterface $propertyFields,
        TokenStorageInterface $tokenStorage

        //WbCharacteristicByObjectName $wbCharacteristic,
        //WbTokenByProfileInterface $wbTokenByProfile,
    )
    {
        $this->propertyFields = $propertyFields;
        //$this->wbCharacteristic = $wbCharacteristic;
        //$this->wbTokenByProfile = $wbTokenByProfile;
        $this->marketProductPropertyCollection = $marketProductPropertyCollection;
        $this->marketParametersRequest = $marketParametersRequest;
        $this->tokenStorage = $tokenStorage;
    }


    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {

            /** @var YaMarketProductsSettingsDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            /** Коллекция свойств категории для выпадающего списка */
            $property_fields = $this->propertyFields->getPropertyFieldsCollection($data->getSettings());

            /**  Добавляем к выбору ТП, варианты и модификации */
            $offer = $this->propertyFields->getOffersFields($data->getSettings());

            if($offer)
            {
                $variation = $this->propertyFields->getVariationFields($offer->getValue());

                if($variation)
                {
                    //$property_fields[] = $variation;

                    $modification = $this->propertyFields->getModificationFields($variation->getValue());

                    if($modification)
                    {
                        array_unshift($property_fields, $modification);
                        array_unshift($property_fields, $variation);
                        array_unshift($property_fields, $offer);
                    }
                    else
                    {
                        array_unshift($property_fields, $variation);
                        array_unshift($property_fields, $offer);
                    }
                }
                else
                {
                    array_unshift($property_fields, $offer);
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



            /* Указывается, если товар:

    лекарство;
    бумажная или электронная книга;
    аудиокнига;
    музыка или видео;
    изготовляется на заказ. */
            //            $ddd = new YaMarketProductsSettingsPropertyDTO();
            //            $ddd->setType('type');
            //            $ddd->setName('Особый тип товара');
            //            //$ddd->setRequired(true);
            //            $characteristics[] = $ddd;


            // $characteristics[1]['name'] = 'manufacturerCountries';
            // $characteristics[2]['name'] = 'boxCount';

            //            if($profile)
            //            {
            //                try
            //                {
            //                    $characteristics = $this->wbCharacteristic
            //                        ->profile($profile)
            //                        ->name($data->getName())
            //                        ->findCharacteristics();
            //
            //
            //
            //                } catch(DomainException $e)
            //                {
            //                    /** Если токен авторизации не найден */
            //                    $characteristics = [];
            //                }
            //            }

            /** @var YaMarketProductsSettingsPropertyDTO $characteristic */
            //foreach($parameters as $param)
            ///{

                //                $new = true;
                //
                //
                //                /** @var YaMarketProductsSettingsPropertyDTO $property */
                //                foreach($data->getProperty() as $property)
                //                {
                //                    if($property->getType() === $characteristic->getName())
                //                    {
                //                        //$property->setRequired($characteristic->isRequired());
                //                        //$property->setUnit($characteristic->getUnit());
                //                        //$property->setPopular($characteristic->isPopular());
                //
                //                        $new = false;
                //                        break;
                //                    }
                //                }

                //                if($new)
                //                {
                //                    /**
                //                     * "objectName": "Косухи", - Наименование подкатегории
                //                     * "name": "Особенности модели", - Наименование характеристики
                //                     * "required": false, - Характеристика обязательна к заполнению
                //                     * "unitName": "", - Единица измерения (см, гр и т.д.)
                //                     * "maxCount": 1, - Максимальное кол-во значений которое можно присвоить данной характеристике
                //                     * "popular": false, - Характеристика популярна у пользователей
                //                     * "charcType": 1 - Тип характеристики (1 и 0 - строка или массив строк; 4 - число или массив чисел)
                //                     */
                //
                //                    $WbProductSettingsPropertyDTO = new YaMarketProductsSettingsPropertyDTO();
                //                    $WbProductSettingsPropertyDTO->setType($characteristic->getName());
                //
                ////                    $WbProductSettingsPropertyDTO->setRequired($characteristic->isRequired());
                ////                    $WbProductSettingsPropertyDTO->setUnit($characteristic->getUnit());
                ////                    $WbProductSettingsPropertyDTO->setPopular($characteristic->isPopular());
                //
                //                    $data->addProperty($WbProductSettingsPropertyDTO);
                //                }

            //}

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
        $builder->add
        (
            'product_settings',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => YaMarketProductsSettingsDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
        ],);
    }

}
