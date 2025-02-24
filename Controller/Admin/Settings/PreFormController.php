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
use BaksDev\Yandex\Market\Products\Forms\Preform\PreformDTO;
use BaksDev\Yandex\Market\Products\Forms\Preform\PreformForm;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_PRODUCTS_SETTING_NEW')]
final class PreFormController extends AbstractController
{
    #[Route('/admin/ya/market/product/setting/preforms', name: 'admin.settings.preform', methods: ['POST', 'GET'])]
    public function delete(
        Request $request,
    ): Response
    {

        $PreformDTO = new PreformDTO();

        $form = $this->createForm(PreformForm::class, $PreformDTO, [
            'action' => $this->generateUrl('yandex-market-products:admin.settings.preform'),
        ]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('ya_market_preform'))
        {
            $this->refreshTokenForm($form);

            return $this->redirectToRoute(
                'yandex-market-products:admin.settings.newedit.new',
                ['id' => $PreformDTO->category, 'market' => $PreformDTO->market->getId()]
            );
        }

        return $this->render(['form' => $form->createView(),]);
    }

}
