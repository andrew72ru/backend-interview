<?php declare(strict_types=1);

namespace App\Tests;

use App\Controller\EmailController;
use Symfony\Component\HttpFoundation\Request;

class UnitControllerTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    public function testCreateEmail(): void
    {
        /** @var EmailController $controller */
        $controller = self::getContainer()->get(EmailController::class);
        $request = Request::createFromGlobals();

        $result = $controller->new($request);

    }
}
