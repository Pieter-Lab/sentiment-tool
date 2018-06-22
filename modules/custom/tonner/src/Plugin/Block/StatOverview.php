<?php

namespace Drupal\tonner\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides a 'StatOverview' block.
 *
 * @Block(
 *  id = "stats_overview",
 *  admin_label = @Translation("Stats Overview"),
 * )
 */
class StatOverview extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $build['stats_overview']['#markup'] = 'Implement StatOverview.';

    return $build;
  }

}
