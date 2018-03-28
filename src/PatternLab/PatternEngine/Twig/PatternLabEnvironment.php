<?php

namespace PatternLab\PatternEngine\Twig;

class PatternLabEnvironment extends \Twig_Environment {

  const REG_EX_COMMENT = '/^{#(.*)#}/s';

  public function __construct(\Twig_LoaderInterface $loader = null, $options = array()) {
    parent::__construct($loader, $options);
    $this->addTokenParser(new TwigDocTokenParser());
  }


  public function compileSource($source, $name = null) {
    /*
     * code to replace '[%' with '{% trans %}' in $source
     * comes here ...
     */

    if (preg_match(self::REG_EX_COMMENT, $source->getCode())) {
      $code = preg_replace(self::REG_EX_COMMENT, "{% twig_doc %}\n{% verbatim %}\n$1\n{% endverbatim %}\n{% endtwig_doc %}", $source->getCode());
      $source = new \Twig_Source($code, $source->getName(), $source->getPath());
    }

    return parent::compileSource($source, $name = null);
  }
}



$t = '/(?:#\-\{\#\s*|\#\})\n?/s';