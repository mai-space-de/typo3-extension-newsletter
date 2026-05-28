<?php

declare(strict_types=1);

namespace Maispace\MaiNewsletter\Tests\Unit\Controller\Backend;

use Maispace\MaiBase\Controller\Backend\AbstractBackendController;
use Maispace\MaiNewsletter\Controller\Backend\NewsletterBackendController;
use Maispace\MaiNewsletter\Domain\Repository\CampaignRepository;
use Maispace\MaiNewsletter\Domain\Repository\SubscriberRepository;
use Maispace\MaiNewsletter\Service\CampaignDispatcher;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Core\Imaging\IconFactory;

final class NewsletterBackendControllerTest extends TestCase
{
    #[Test]
    public function controllerExtendsAbstractBackendController(): void
    {
        self::assertTrue(
            is_subclass_of(NewsletterBackendController::class, AbstractBackendController::class),
        );
    }

    #[Test]
    public function constructorDeclaresModuleTemplateFactoryParameter(): void
    {
        $params = (new \ReflectionMethod(NewsletterBackendController::class, '__construct'))
            ->getParameters();

        $names = array_map(static fn(\ReflectionParameter $p) => $p->getName(), $params);
        self::assertContains('moduleTemplateFactory', $names);

        $factoryParam = array_values(array_filter(
            $params,
            static fn(\ReflectionParameter $p) => $p->getName() === 'moduleTemplateFactory',
        ))[0];

        $type = $factoryParam->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(ModuleTemplateFactory::class, $type->getName());
    }

    #[Test]
    public function constructorDeclaresIconFactoryParameter(): void
    {
        $params = (new \ReflectionMethod(NewsletterBackendController::class, '__construct'))
            ->getParameters();

        $names = array_map(static fn(\ReflectionParameter $p) => $p->getName(), $params);
        self::assertContains('iconFactory', $names);

        $iconParam = array_values(array_filter(
            $params,
            static fn(\ReflectionParameter $p) => $p->getName() === 'iconFactory',
        ))[0];

        $type = $iconParam->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(IconFactory::class, $type->getName());
    }

    #[Test]
    public function constructorRequiresSubscriberRepository(): void
    {
        $params = (new \ReflectionMethod(NewsletterBackendController::class, '__construct'))
            ->getParameters();

        $names = array_map(static fn(\ReflectionParameter $p) => $p->getName(), $params);
        self::assertContains('subscriberRepository', $names);

        $repoParam = array_values(array_filter(
            $params,
            static fn(\ReflectionParameter $p) => $p->getName() === 'subscriberRepository',
        ))[0];

        $type = $repoParam->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(SubscriberRepository::class, $type->getName());
    }

    #[Test]
    public function constructorRequiresCampaignRepository(): void
    {
        $params = (new \ReflectionMethod(NewsletterBackendController::class, '__construct'))
            ->getParameters();

        $names = array_map(static fn(\ReflectionParameter $p) => $p->getName(), $params);
        self::assertContains('campaignRepository', $names);

        $repoParam = array_values(array_filter(
            $params,
            static fn(\ReflectionParameter $p) => $p->getName() === 'campaignRepository',
        ))[0];

        $type = $repoParam->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(CampaignRepository::class, $type->getName());
    }

    #[Test]
    public function indexActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'indexAction'),
        );
    }

    #[Test]
    public function indexActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'indexAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }

    #[Test]
    public function exportCsvActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'exportCsvAction'),
        );
    }

    #[Test]
    public function exportCsvActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'exportCsvAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }

    #[Test]
    public function constructorDeclaresCampaignDispatcherParameter(): void
    {
        $params = (new \ReflectionMethod(NewsletterBackendController::class, '__construct'))
            ->getParameters();

        $names = array_map(static fn(\ReflectionParameter $p) => $p->getName(), $params);
        self::assertContains('campaignDispatcher', $names);

        $dispatcherParam = array_values(array_filter(
            $params,
            static fn(\ReflectionParameter $p) => $p->getName() === 'campaignDispatcher',
        ))[0];

        $type = $dispatcherParam->getType();
        self::assertInstanceOf(\ReflectionNamedType::class, $type);
        self::assertSame(CampaignDispatcher::class, $type->getName());
    }

    #[Test]
    public function sendActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'sendAction'),
        );
    }

    #[Test]
    public function sendActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'sendAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }

    #[Test]
    public function scheduleActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'scheduleAction'),
        );
    }

    #[Test]
    public function scheduleActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'scheduleAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }

    #[Test]
    public function approvePendingActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'approvePendingAction'),
        );
    }

    #[Test]
    public function approvePendingActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'approvePendingAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }

    #[Test]
    public function rejectPendingActionMethodExists(): void
    {
        self::assertTrue(
            method_exists(NewsletterBackendController::class, 'rejectPendingAction'),
        );
    }

    #[Test]
    public function rejectPendingActionReturnsResponseInterface(): void
    {
        $returnType = (new \ReflectionMethod(NewsletterBackendController::class, 'rejectPendingAction'))
            ->getReturnType();

        self::assertInstanceOf(\ReflectionNamedType::class, $returnType);
        self::assertSame(ResponseInterface::class, $returnType->getName());
    }
}
