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

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Cards;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterDTO;
use BaksDev\Products\Product\Forms\ProductFilter\Admin\ProductFilterForm;
use BaksDev\Yandex\Market\Products\Repository\Card\PaginatorYaMarketProductsCard\PaginatorYaMarketProductsCardInterface;
use BaksDev\Yandex\Market\Products\Repository\Cards\AllWbProductCard\AllWbProductCardInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_PRODUCTS_CARD')]
final class IndexController extends AbstractController
{
    /** Карточки товаров Yandex Market  */
    #[Route('/admin/ya/market/product/cards/{page<\d+>}', name: 'admin.card.index', methods: ['GET', 'POST'])]
    public function index(
        Request $request,
        PaginatorYaMarketProductsCardInterface $paginatorYaMarketProductsCard,
        int $page = 0,
    ): Response {

        /* Поиск */
        $search = new SearchDTO($request);
        $searchForm = $this->createForm(
            SearchForm::class,
            $search,
            ['action' => $this->generateUrl('yandex-market-products:admin.card.index')]
        );
        $searchForm->handleRequest($request);


        /**
         * Фильтр продукции по ТП
         */
        $filter = new ProductFilterDTO($request);
        $filterForm = $this->createForm(ProductFilterForm::class, $filter, [
            'action' => $this->generateUrl('yandex-market-products:admin.card.index'),
        ]);
        $filterForm->handleRequest($request);
        !$filterForm->isSubmitted() ?: $this->redirectToReferer();


        /* Получаем список карточек */
        $query = $paginatorYaMarketProductsCard
            ->filter($filter)
            ->search($search)
            ->findPaginator();


        return $this->render(
            [
                'query' => $query,
                'search' => $searchForm->createView(),
                'filter' => $filterForm->createView(),
            ],
        );
    }

}
