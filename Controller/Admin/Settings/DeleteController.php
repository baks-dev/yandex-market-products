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

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Settings;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Yandex\Market\Products\Entity\Settings\Event\YaMarketProductsSettingsEvent;
use BaksDev\Yandex\Market\Products\Entity\Settings\YaMarketProductsSettings;
use BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\DeleteYaMarketProductsSettingsDTO;
use BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\DeleteYaMarketProductsSettingsForm;
use BaksDev\Yandex\Market\Products\UseCase\Settings\Delete\DeleteYaMarketProductsSettingsHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_PRODUCTS_SETTING_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/ya/market/product/setting/delete/{id}', name: 'admin.settings.delete', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
        DeleteYaMarketProductsSettingsHandler $ProductSettingsHandler,
        #[MapEntity] YaMarketProductsSettingsEvent $Event,
    ): Response
    {

        $DeleteWbProductSettingsDTO = new DeleteYaMarketProductsSettingsDTO();
        $Event->getDto($DeleteWbProductSettingsDTO);

        $form = $this->createForm(DeleteYaMarketProductsSettingsForm::class, $DeleteWbProductSettingsDTO, [
            'action' => $this->generateUrl('yandex-market-products:admin.settings.delete', ['id' => $DeleteWbProductSettingsDTO->getEvent()]),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('delete_market_products_settings'))
        {
            $this->refreshTokenForm($form);

            $WbProductSettings = $ProductSettingsHandler->handle($DeleteWbProductSettingsDTO);

            if($WbProductSettings instanceof YaMarketProductsSettings)
            {
                $this->addFlash('page.delete', 'success.delete', 'yandex-market-products.admin.settings');

                return $this->redirectToRoute('yandex-market-products:admin.settings.index');
            }

            $this->addFlash(
                'page.delete',
                'danger.delete',
                'yandex-market-products.admin.settings',
                $WbProductSettings,
            );

            return $this->redirectToRoute('yandex-market-products:admin.settings.index', status: 400);

        }

        return $this->render([
            'form' => $form->createView(),
            //'name' => $Event->getName(),
        ],);
    }

}
