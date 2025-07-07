<?php

use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;
use Drupal\panelizer\Entity\Panelizer;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionComponent;

/**
 * Scans all node revisions using Panelizer and removes broken block references.
 */
function cleanup_panelizer_broken_blocks() {
  $node_storage = \Drupal::entityTypeManager()->getStorage('node');
  $nids = $node_storage->getQuery()->accessCheck(FALSE)->execute();

  foreach ($nids as $nid) {
    $revisions = $node_storage->revisionIds(Node::load($nid));
    foreach ($revisions as $vid) {
      $node = $node_storage->loadRevision($vid);
      if (!$node || !$node->hasField('panelizer')) {
        continue;
      }

      $panelizer_field = $node->get('panelizer')->first();
      if (!$panelizer_field) {
        continue;
      }

      $layout_data = $panelizer_field->get('panelizer_view_mode')->getValue();
      if (empty($layout_data)) {
        continue;
      }

      $sections = unserialize($layout_data);
      $modified = FALSE;

      foreach ($sections as $section_index => $section) {
        if (!$section instanceof Section) {
          continue;
        }

        foreach ($section->getComponents() as $component) {
          $config = $component->getPlugin()->getConfiguration();

          if (isset($config['block_revision_id'])) {
            $block = BlockContent::loadRevision($config['block_revision_id']);
            if (!$block) {
              // Replace with a placeholder block or remove the component
              echo "❌ Missing block in Node $nid, Revision $vid. Removing component...\n";
              $section->removeComponent($component->getUuid());
              $modified = TRUE;
            }
          }
        }

        if ($modified) {
          $sections[$section_index] = $section;
        }
      }

      if ($modified) {
        $panelizer_field->set('panelizer_view_mode', serialize($sections));
        $node->save();
        echo "✅ Cleaned Node $nid, Revision $vid\n";
      }
    }
  }
}
