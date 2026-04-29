<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
 *
 */

declare(strict_types=1);

namespace BaksDev\Yandex\Market\Products\Controller\Admin\Barcode;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Yandex\Market\Products\Entity\Barcode\Event\YaMarketBarcodeEvent;
use BaksDev\Yandex\Market\Products\Entity\Barcode\YaMarketBarcode;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\YaMarketBarcodeDeleteDTO;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\YaMarketBarcodeDeleteForm;
use BaksDev\Yandex\Market\Products\UseCase\Barcode\Delete\YaMarketBarcodeDeleteHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_YA_MARKET_BARCODE_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/yandex/barcode/delete/{id}', name: 'admin.barcode.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] YaMarketBarcodeEvent $YaMarketBarcodeEvent,
        YaMarketBarcodeDeleteHandler $YaMarketBarcodeDeleteHandler,
    ): Response
    {
        $profile = $this->getProfileUid();


        $YaMarketBarcodeDeleteDTO = new YaMarketBarcodeDeleteDTO();
        $YaMarketBarcodeEvent->getDto($YaMarketBarcodeDeleteDTO);


        $form = $this->createForm(
            type: YaMarketBarcodeDeleteForm::class,
            data: $YaMarketBarcodeDeleteDTO,
            options: [
                'action' => $this->generateUrl('yandex-market-products:admin.barcode.delete',
                    ['id' => $YaMarketBarcodeDeleteDTO->getEvent()],
                ),
            ]);

        $form->handleRequest($request);


        if($form->isSubmitted() && $form->isValid() && $form->has('yandex_market_barcode_delete'))
        {
            $handle = $YaMarketBarcodeDeleteHandler->handle($YaMarketBarcodeDeleteDTO, $profile);

            $this->addFlash
            (
                'admin.page.delete',
                $handle instanceof YaMarketBarcode ? 'admin.success.delete' : 'admin.danger.delete',
                'yandex-market-products.barcode',
                $handle,
            );

            return $this->redirectToRoute('yandex-market-products:admin.barcode.index');
        }


        return $this->render(['form' => $form->createView()]);
    }
}