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

namespace BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\Property;

//use App\Module\Products\Category\Repository\PropertyFieldsByCategoryChoiceForm\PropertyFieldsByCategoryChoiceFormInterface;
//use App\Module\Products\Category\Type\Id\CategoryUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class YaMarketProductsSettingsPropertyForm extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        /* TextType */
        //$builder->add('type', HiddenType::class);

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
                            'choices' =>  array_combine($YaMarketProperty->choices(), $YaMarketProperty->choices()),
                            'expanded' => false,
                            'multiple' => false,
                            'translation_domain' => 'yandex-market-products.property',
                            'data' => $data->getDef() ?: $YaMarketProperty->default(),
                            'required' => $YaMarketProperty->required(),
                        ]);
                }
                else
                {
                    $form->add('def', TextType::class,
                        [
                            'data' => $data->getDef() ?: $YaMarketProperty->default(),
                            'required' => $YaMarketProperty->required()
                        ]);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults
        (
            [
                'data_class' => YaMarketProductsSettingsPropertyDTO::class,
                'property_fields' => null,
            ]);
    }

}
