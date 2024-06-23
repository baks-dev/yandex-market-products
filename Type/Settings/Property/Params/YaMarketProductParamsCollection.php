<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Yandex\Market\Products\Type\Settings\Property\Params;

use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

final readonly class YaMarketProductParamsCollection
{
    public function __construct(
        #[AutowireIterator('baks.ya.product.params', defaultPriorityMethod: 'priority')]
        private iterable $params,
    ) {}

    public function cases(int $category): array
    {
        $case = null;

        foreach($this->params as $key => $params)
        {
            if($params::CATEGORY !== $category)
            {
                continue;
            }

            $case[$key] = new $params();
        }

        return $case;
    }





//    public function casesCategory(int $category): array
//    {
//        $case = null;
//
//        /** @var YaMarketProductParamsInterface $instance */
//        foreach($this->params as $key => $params)
//        {
//            $instance = new $params();
//
//            if($instance->isSetting())
//            {
//                $case[$key] = $instance;
//            }
//        }
//
//        return $case;
//    }
//
//    public function casesCards(int $category): array
//    {
//        $case = null;
//
//        /** @var YaMarketProductParamsInterface $instance */
//
//        foreach($this->params as $key => $params)
//        {
//            if($params::CATEGORY !== $category)
//            {
//                continue;
//            }
//
//            $instance = new $params();
//
//            if($instance->isCard())
//            {
//                $case[$key] = $instance;
//            }
//        }
//
//        return $case;
//    }

}
