<?php

namespace PatternLab\PatternEngine\Twig;

class PatternLabEnvironment extends \Twig_Environment {

  const REG_EX_COMMENT = '/^{#(.*)#}/s';

  public function __construct(\Twig_LoaderInterface $loader = null, $options = array()) {
    parent::__construct($loader, $options);
    $this->addTokenParser(new TwigDocTokenParser());
    $this->addExtension(new \Twig_Extension_StringLoader);
  }

  public function compileSource($source, $name = null) {
    if (preg_match(self::REG_EX_COMMENT, $source->getCode())) {
      $path = realpath(__DIR__ . "/../../../../template/twig-doc-output.twig" );
      $content = \file_get_contents($path);
      $code = preg_replace(self::REG_EX_COMMENT, "{% twig_doc %}\n{% verbatim %}\n$1\n{% endverbatim %}\n{% endtwig_doc %} " . $content, $source->getCode());
      $source = new \Twig_Source($code, $source->getName(), $source->getPath());
    }
    return parent::compileSource($source, $name);
  }
}