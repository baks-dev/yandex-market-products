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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class YaMarketProductsSettingsPropertyForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) use ($options) {

            /** @var YaMarketProductsSettingsPropertyDTO $data */
            $data = $event->getData();
            $form = $event->getForm();

            if($data)
            {
                $YaMarketProperty = $data->getType()?->getYaMarketProductProperty();

                $form
                    ->add('field', ChoiceType::class, [
                        'choices' => $options['property_fields'],  // array_flip(Main::LANG),

                        'choice_value' => function($type) {
                            return $type?->getValue();
                        },

                        'choice_label' => function($type) {
                            return $type->getAttr();
                        },

                        'label' => $data->getType(),
                        'help' => $data->getType().'_desc',
                        'expanded' => false,
                        'multiple' => false,
                        'translation_domain' => 'yandex-market-products.property',
                        'required' => false,
                        //'disabled' => !$data->isIsset()
                    ]);


                if($YaMarketProperty && $YaMarketProperty->choices())
                {
                    $form
                        ->add('def', ChoiceType::class, [
                            'choices' => array_combine($YaMarketProperty->choices(), $YaMarketProperty->choices()),
                            'expanded' => false,
                            'multiple' => false,
                            'translation_domain' => 'yandex-market-products.property',
                            'data' => $data->getDef() ?: $YaMarketProperty->default(),
                            'required' => $YaMarketProperty->required(),
                        ]);
                }
                else
                {
                    $form->add(
                        'def',
                        TextType::class,
                        [
                            'data' => $data->getDef() ?: $YaMarketProperty->default(),
                            'required' => $YaMarketProperty->required()
                        ]
                    );
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(
            [
                'data_class' => YaMarketProductsSettingsPropertyDTO::class,
                'property_fields' => null,
            ]
        );
    }

}
