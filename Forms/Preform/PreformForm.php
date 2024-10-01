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

namespace BaksDev\Yandex\Market\Products\Forms\Preform;

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Api\Reference\Category\YandexMarketCategoryDTO;
use BaksDev\Yandex\Market\Products\Api\Reference\Category\YaMarketGetCategoriesTreeRequest;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class PreformForm extends AbstractType
{
    private CategoryChoiceInterface $categoryChoice;
    private TokenStorageInterface $tokenStorage;
    private YaMarketGetCategoriesTreeRequest $yandexMarketCategoryRequest;

    public function __construct(
        TokenStorageInterface $tokenStorage,
        YaMarketGetCategoriesTreeRequest $yandexMarketCategoryRequest,
        CategoryChoiceInterface $categoryChoice,
    ) {
        $this->categoryChoice = $categoryChoice;
        $this->tokenStorage = $tokenStorage;
        $this->yandexMarketCategoryRequest = $yandexMarketCategoryRequest;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choices' => $this->categoryChoice->findAll(),
                'choice_value' => function (?CategoryProductUid $type) {
                    return $type?->getValue();
                },
                'choice_label' => function (CategoryProductUid $type) {
                    return $type?->getOptions();
                },

                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => true,
            ]);


        /** Получаем список параметров категории маркет по токену профиля */

        $TokenInterface = $this->tokenStorage->getToken();
        $UserProfileUid = $TokenInterface?->getUser()?->getProfile();

        if($UserProfileUid)
        {

            $market = $this->yandexMarketCategoryRequest
                ->profile($UserProfileUid)
                ->findAll();

            /** @var YandexMarketCategoryDTO $YandexMarketCategoryDTO */

            $builder
                ->add('market', ChoiceType::class, [
                    'choices' => $market,  // array_flip(Main::LANG),
                    'choice_value' => function (?YandexMarketCategoryDTO $type) {
                        return $type?->getId();
                    },
                    'choice_label' => function (YandexMarketCategoryDTO $type) {
                        return $type?->getName();
                    },

                    'expanded' => false,
                    'multiple' => false,
                    //'required' => $data->isRequired(),
                    //'disabled' => !$data->isIsset()
                ]);

        }


        /* Сохранить ******************************************************/
        $builder->add(
            'ya_market_preform',
            SubmitType::class,
            ['label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => PreformDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }

}
