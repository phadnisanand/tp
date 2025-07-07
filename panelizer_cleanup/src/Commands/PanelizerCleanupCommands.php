<?php

namespace Drupal\panelizer_cleanup\Commands;

use Drush\Commands\DrushCommands;
use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;

/**
 * Drush command to clean up broken block references in Panelizer layouts.
 */
class PanelizerCleanupCommands extends DrushCommands {

  /**
   * Cleans up broken block_content references in Panelizer layouts.
   *
   * @command panelizer:cleanup-broken-blocks
   * @aliases pcb
   */
  public function cleanup($panelizer_field = 'field_panelizer') {
    $node_storage = \Drupal::entityTypeManager()->getStorage('node');
    $nids = $node_storage->getQuery()->accessCheck(FALSE)->execute();

    foreach ($nids as $nid) {
      $revision_ids = $node_storage->getQuery()
        ->allRevisions()
        ->condition('nid', $nid)
        ->sort('vid', 'ASC')
        ->execute();

      foreach ($revision_ids as $vid) {
        $revision = $node_storage->loadRevision($vid);
        if (!$revision || !$revision->hasField($panelizer_field)) {
          continue;
        }

        $panelizer_data = $revision->get($panelizer_field)->getValue();
        if (empty($panelizer_data[0]['value'])) {
          continue;
        }

        $layout = @unserialize($panelizer_data[0]['value']);
        if (!is_array($layout)) {
          continue;
        }

        $modified = FALSE;

        foreach ($layout as &$section) {
          if (!isset($section['components'])) {
            continue;
          }

          foreach ($section['components'] as $key => $component) {
            if (isset($component['configuration']['block_uuid'])) {
              $uuid = $component['configuration']['block_uuid'];
              $block = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $uuid]);
              if (empty($block)) {
                $this->output()->writeln("❌ Missing block UUID $uuid in Node $nid, Revision $vid. Removing component...");
                unset($section['components'][$key]);
                $modified = TRUE;
              }
            }
          }
        }

        if ($modified) {
          $revision->set($panelizer_field, serialize($layout));
          $revision->save();
          $this->output()->writeln("✅ Cleaned Node $nid, Revision $vid");
        }
      }
    }

    $this->output()->writeln("🎉 Cleanup complete.");
  }
}
