<?php
namespace PatternLab\PatternEngine\Twig;

use \Twig_Token;
use Symfony\Component\Yaml\Yaml;

class TwigDocTokenParser extends \Twig_TokenParser
{
    public function parse(\Twig_Token $token)
    {
      $lineno = $token->getLine();
      $stream = $this->parser->getStream();
      $body = NULL;

      $stream->expect(\Twig_Token::BLOCK_END_TYPE);
      $body = $this->parser->subparse([$this, 'decideCacheEnd'], true);
      $stream->expect(\Twig_Token::BLOCK_END_TYPE);
      $node = new TwigDocNode($body, $lineno, $this->getTag());
      return $node;
    }

    public function getTag()
    {
        return 'twig_doc';
    }

    public function decideCacheEnd(\Twig_Token $token)
    {
        return $token->test('endtwig_doc');
    }

}

class TwigDocNode extends \Twig_Node {

  public function __construct(\Twig_Node $body, $lineno, $tag = NULL) {
    parent::__construct([
      'body' => $body,
    ], [], $lineno, $tag);
  }

  public function compile(\Twig_Compiler $compiler) {
    $compiler->addDebugInfo($this);
    $body = $this->getNode('body');

    $content = '';


    $data = $body->getNode(0)->getAttribute('data');


    $factory  = \phpDocumentor\Reflection\DocBlockFactory::createInstance();
    $docblock = $factory->create($data);

    $info = [
      'doc' => [
        'summary' => $docblock->getSummary(),
        'description' => $docblock->getDescription()->render(),
      ],
    ];

    foreach ($docblock->getTags() as $tag) {

      $class = get_class($tag);
      $tagInfo = ['type' => 'generic'];

      if (!isset($info['doc']['tags'][$tag->getName()])) {
        $info['doc']['tags'][$tag->getName()] = [];
      }

      print_r($class . "\n") ;

      switch ($class) {

        case 'phpDocumentor\Reflection\DocBlock\Tags\Param':
        $info['doc']['tags'][$tag->getName()][$tag->getVariableName()] = [
          'type' => $tag->getType()->__toString(),
          'description' => $tag->getDescription()->render()
        ];
        continue;

        case 'phpDocumentor\Reflection\DocBlock\Tags\Author':
          $info['doc']['tags'][$tag->getName()] = [
            'name' => $tag->getAuthorName(),
            'email' => $tag->getEmail(),
          ];
        continue;

        default:
          $info['doc']['tags'][$tag->getName()] = $tag->getDescription()->render();
      }

    }

    print_r($info);

    $compiler->write('if (!isset($context["twig"])) { $context["twig"] = array(); }')
             ->raw(";\n");

  // merge the data provided in this template with the existing data
    $compiler->write('$context["twig"] = array_merge($context["twig"], ')
             ->repr($info)
             ->raw(");\n");
  }

}