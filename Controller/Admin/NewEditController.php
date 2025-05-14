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

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Yandex\Market\Products\Entity\YandexMarketProduct;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketProductDTO;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketProductForm;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketProductHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_PRODUCTS_EDIT')]
class NewEditController extends AbstractController
{
    #[Route(
        '/admin/ya/market/product/edit/{invariable}',
        name: 'admin.products.edit',
        methods: ['GET', 'POST']
    )]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        YandexMarketProductHandler $handler,
        ProductDetailByInvariableInterface $productDetailByInvariable,
        string|null $invariable = null,
    ): Response
    {
        $dto = new YandexMarketProductDTO();

        $dto->setInvariable($invariable);

        /**
         * Находим уникальный продукт Яндекс Маркет, делаем его инстанс, передаем в форму
         *
         * @var YandexMarketProduct|null $yandexMarketProductCard
         */
        $yandexMarketProductCard = $entityManager->getRepository(YandexMarketProduct::class)
            ->findOneBy([
                'invariable' => $invariable,
            ]);

        $yandexMarketProductCard?->getDto($dto);

        $form = $this->createForm(
            YandexMarketProductForm::class,
            $dto,
            ['action' => $this->generateUrl(
                'yandex-market-products:admin.products.edit',
                [
                    'invariable' => $dto->getInvariable(),
                ]
            )]
        );

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('yandex_market_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $handler->handle($dto);

            $this->addFlash(
                'page.edit',
                $handle instanceof YandexMarketProduct ? 'success.edit' : 'danger.edit',
                'yandex-market-products.admin',
                $handle
            );

            return $this->redirectToRoute('yandex-market-products:admin.products.index');
        }

        $yandexMarketProduct = $productDetailByInvariable
            ->invariable($invariable)
            ->find()
            ->current();

        return $this->render([
            'form' => $form->createView(),
            'product' => $yandexMarketProduct,
        ]);
    }
}