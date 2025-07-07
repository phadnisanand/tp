use Drupal\node\Entity\Node;
use Drupal\block_content\Entity\BlockContent;
use Drupal\panelizer\Entity\Panelizer;

$nids = \Drupal::entityQuery('node')->accessCheck(FALSE)->execute();

foreach ($nids as $nid) {
  $revisions = \Drupal::entityTypeManager()->getStorage('node')->revisionIds(Node::load($nid));
  foreach ($revisions as $vid) {
    $node = \Drupal::entityTypeManager()->getStorage('node')->loadRevision($vid);
    if (!$node || !$node->hasField('panelizer')) {
      continue;
    }

    $panelizer = $node->get('panelizer')->first();
    if (!$panelizer) {
      continue;
    }

    $layout = $panelizer->get('panelizer_view_mode')->getValue();
    if (strpos($layout, 'block_content') !== FALSE) {
      preg_match_all('/block_content:([a-f0-9\-]+)/', $layout, $matches);
      foreach ($matches[1] as $uuid) {
        $block = \Drupal::entityTypeManager()->getStorage('block_content')->loadByProperties(['uuid' => $uuid]);
        if (empty($block)) {
          echo "❌ Missing block UUID $uuid in Node $nid, Revision $vid\n";
        }
      }
    }
  }
}
