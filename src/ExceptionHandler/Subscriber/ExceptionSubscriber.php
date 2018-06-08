<?php

namespace App\ExceptionHandler\Subscriber;

use App\Request\Subscriber\RequestStudyAreaSubscriber;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;

class ExceptionSubscriber implements EventSubscriberInterface
{

  /**
   * @return array
   */
  public static function getSubscribedEvents()
  {
    return [KernelEvents::EXCEPTION => array(array('onKernelException', 0))];
  }

  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    $exception = $event->getException();

    if ($exception instanceof AccessDeniedHttpException) {
      // Clear cached study area on 403
      $event->getRequest()->getSession()->remove(RequestStudyAreaSubscriber::STUDY_AREA_KEY);
    }
  }
}