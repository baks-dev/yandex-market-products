<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Forms\Preform;

use BaksDev\Products\Category\Repository\CategoryChoice\CategoryChoiceInterface;
use BaksDev\Products\Category\Type\Id\CategoryProductUid;
use BaksDev\Yandex\Market\Products\Api\Reference\Category\YaMarketGetCategoriesTreeRequest;
use BaksDev\Yandex\Market\Products\Api\Reference\Category\YandexMarketCategoryDTO;
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
    )
    {
        $this->categoryChoice = $categoryChoice;
        $this->tokenStorage = $tokenStorage;
        $this->yandexMarketCategoryRequest = $yandexMarketCategoryRequest;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('category', ChoiceType::class, [
                'choices' => $this->categoryChoice->findAll(),
                'choice_value' => function(?CategoryProductUid $type) {
                    return $type?->getValue();
                },
                'choice_label' => function(CategoryProductUid $type) {
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
                    'choice_value' => function(?YandexMarketCategoryDTO $type) {
                        return $type?->getId();
                    },
                    'choice_label' => function(YandexMarketCategoryDTO $type) {
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
