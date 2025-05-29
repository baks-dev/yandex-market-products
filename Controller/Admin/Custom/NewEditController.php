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

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Custom;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Repository\ProductDetail\ProductDetailByInvariableInterface;
use BaksDev\Yandex\Market\Products\Entity\Custom\YandexMarketProductCustom;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketCustomProductDTO;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketCustomProductForm;
use BaksDev\Yandex\Market\Products\UseCase\NewEdit\YandexMarketCustomProductHandler;
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
        '/admin/ya/market/custom/edit/{invariable}',
        name: 'admin.custom.edit',
        methods: ['GET', 'POST']
    )]
    public function index(
        Request $request,
        EntityManagerInterface $entityManager,
        YandexMarketCustomProductHandler $YandexMarketProductHandler,
        ProductDetailByInvariableInterface $productDetailByInvariable,
        string|null $invariable = null,
    ): Response
    {
        $YandexMarketCustomProductDTO = new YandexMarketCustomProductDTO()
            ->setInvariable($invariable);

        /**
         * Находим уникальный продукт Яндекс Маркет, делаем его инстанс, передаем в форму
         *
         * @var YandexMarketProductCustom|null $yandexMarketProductCard
         */
        $yandexMarketProductCard = $entityManager
            ->getRepository(YandexMarketProductCustom::class)
            ->findOneBy(['invariable' => $invariable]);


        $yandexMarketProductCard?->getDto($YandexMarketCustomProductDTO);

        $form = $this
            ->createForm(
                type: YandexMarketCustomProductForm::class,
                data: $YandexMarketCustomProductDTO,
                options: ['action' => $this->generateUrl(
                    'yandex-market-products:admin.custom.edit', ['invariable' => $YandexMarketCustomProductDTO->getInvariable(),],
                )],
            )
            ->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('yandex_market_product'))
        {
            $this->refreshTokenForm($form);

            $handle = $YandexMarketProductHandler->handle($YandexMarketCustomProductDTO);

            $this->addFlash(
                'page.edit',
                $handle instanceof YandexMarketProductCustom ? 'success.edit' : 'danger.edit',
                'yandex-market-products.admin',
                $handle,
            );

            return $this->redirectToRoute('yandex-market-products:admin.custom.index');
        }

        $yandexMarketProduct = $productDetailByInvariable
            ->invariable($invariable)
            ->find();

        return $this->render([
            'form' => $form->createView(),
            'product' => $yandexMarketProduct,
        ]);
    }
}