<?php

namespace MediaWiki\Extension\PkgStore;

use MWException;
use OutputPage, Parser, PPFrame, Skin;

/**
 * Class MW_EXT_Align
 */
class MW_EXT_Align
{
  /**
   * Get align.
   *
   * @param $id
   *
   * @return array
   */
  private static function getAlign($id): array
  {
    $align = MW_EXT_Kernel::getYAML(__DIR__ . '/store/' . $id . '.yml');
    return $align ?? [] ?: [];
  }

  /**
   * Get align type.
   *
   * @param $id
   * @param $type
   *
   * @return string
   */
  private static function getType($id, $type): string
  {
    $id = self::getAlign($id) ? self::getAlign($id) : '';
    return $id[$type] ?? '' ?: '';
  }

  /**
   * Register tag function.
   *
   * @param Parser $parser
   *
   * @return void
   * @throws MWException
   */
  public static function onParserFirstCallInit(Parser $parser): void
  {
    $parser->setHook('align', [__CLASS__, 'onRenderTag']);
  }

  /**
   * Render tag function.
   *
   * @param $input
   * @param array $args
   * @param Parser $parser
   * @param PPFrame $frame
   *
   * @return string|null
   */
  public static function onRenderTag($input, array $args, Parser $parser, PPFrame $frame): ?string
  {
    // Argument: id.
    $getID = MW_EXT_Kernel::outClear($args['id'] ?? '' ?: '');
    $outID = MW_EXT_Kernel::outNormalize($getID);

    // Argument: type.
    $getType = MW_EXT_Kernel::outClear($args['type'] ?? '' ?: '');
    $outType = MW_EXT_Kernel::outNormalize($getType);

    // Check note type, set error category.
    if (!self::getAlign($outID) || !self::getType($outID, $outType)) {
      $parser->addTrackingCategory('mw-align-error-category');

      return null;
    }

    // Get content.
    $getContent = trim($input);
    $outContent = $parser->recursiveTagParse($getContent, $frame);

    // Out parser.
    return '<div class="mw-align mw-align-' . $outID . '-' . $outType . '">' . $outContent . '</div>';
  }

  /**
   * Load resource function.
   *
   * @param OutputPage $out
   * @param Skin $skin
   *
   * @return void
   */
  public static function onBeforePageDisplay(OutputPage $out, Skin $skin): void
  {
    $out->addModuleStyles(['ext.mw.align.styles']);
  }
}
