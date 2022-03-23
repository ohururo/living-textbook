<?php

namespace App\UrlUtils\Model;

abstract class AbstractUrl
{
  /** @var string */
  protected $url;

  /** AbstractUrl constructor. */
  public function __construct(string $url)
  {
    $this->url       = $url;
  }

  /** @return string */
  public function getUrl(): string
  {
    return $this->url;
  }
}
