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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Parameters;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class YaMarketProductsSettingsParametersForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {

            /** @var YaMarketProductsSettingsParametersDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            if($data)
            {
                $form
                    ->add('field', ChoiceType::class, [
                        'choices' => $options['property_fields'],  // array_flip(Main::LANG),
                        'choice_value' => function ($type) {
                            return $type?->getValue();
                        },
                        'choice_label' => function ($type) {
                            return $type->getAttr();
                        },

                        'label' => $data->getName(),
                        'expanded' => false,
                        'multiple' => false,
                        'required' => false,
                        //'disabled' => !$data->isIsset()
                    ]);


                //                if(is_array($data->getDefault()))
                //                {
                //
                //                    $values = array_map(function($item) {
                //                        return $item["value"];
                //                    }, $data->getDefault());
                //
                //
                //                    $form
                //                        ->add('default', ChoiceType::class, [
                //                            'choices' =>  array_values($values),
                //
                //                            'choice_value' => function($type) {
                //                                return is_array($type) ? null  : $type;
                //                            },
                //                            'choice_label' => function($type) {
                //                                return is_array($type) ? null : $type;
                //                            },
                //
                //                            'expanded' => false,
                //                            'multiple' => false,
                //                            //'translation_domain' => 'yandex-market-products.property',
                //                            //'data' => $YaMarketProperty->default()
                //                        ]);
                //                }
                //                else
                //                {
                //                    $form->add('default', TextType::class);
                //                }

                //                /dump($data);

                //                if(!$data->getType())
                //                {
                //                    $form->remove('field');
                //                }

            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => YaMarketProductsSettingsParametersDTO::class,
                'property_fields' => null,
            ]
        );
    }

}
