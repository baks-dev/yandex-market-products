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

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Settings;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Products\Category\Entity\CategoryProduct;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsForm;
use BaksDev\Yandex\Market\Products\UseCase\Settings\NewEdit\YaMarketProductsSettingsHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_PRODUCTS_SETTING_NEW')]
final class NewController extends AbstractController
{
    #[Route('/admin/ya/market/product/setting/new/{id}/{market<\d+>}',
        name: 'admin.settings.newedit.new',
        requirements: [ 'id' => '^[0-9a-f]{8}(?:-[0-9a-f]{4}){3}-[0-9a-f]{12}$'],
        methods: ['GET', 'POST',])]
    public function new(
        Request $request,
        YaMarketProductsSettingsHandler $productsSettingsHandler,
        #[MapEntity] CategoryProduct $Category,
        int $market,
    ): Response
    {

        $SettingsDTO = new YaMarketProductsSettingsDTO();
        $SettingsDTO->setSettings($Category);
        $SettingsDTO->setMarket($market);

        /* Форма добавления */
        $form = $this->createForm(YaMarketProductsSettingsForm::class, $SettingsDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('product_settings'))
        {
            $handle = $productsSettingsHandler->handle($SettingsDTO);

            if($handle instanceof YaMarketProductsSettings)
            {
                $this->addFlash('admin.page.new', 'admin.success.new', 'admin.wb.products.settings');

                return $this->redirectToRoute('yandex-market-products:admin.settings.index');
            }

            $this->addFlash('admin.page.new', 'admin.danger.new', 'admin.wb.products.settings');

            return $this->redirectToReferer();
        }

        return $this->render([
            'form' => $form->createView()
        ]);

    }
}