<?php

namespace Drupal\migrate_custom\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
 
/**
 * @file
 * Contains \Drupal\migrate_custom\Plugin\migrate\source\Node.
 */

/**
 * Extract nodes from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "news_nodes"
 * )
 */
class NewsNodes extends SqlBase {
 
  /**
   * {@inheritdoc}
   */
  public function query() {
    // this queries the built-in metadata, but not the body, tags, or images.
    $query = $this->select('node', 'n')
      ->condition('n.type', 'article')
      ->fields('n', array(
        'nid',
        'vid',
        'type',
        'language',
        'title',
        'uid',
        'status',
        'created',
        'changed',
        'promote',
        'sticky',
      ));
    $query->orderBy('nid');
    return $query;
  }
 
  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['body/format'] = $this->t('Format of body');
    $fields['body/value'] = $this->t('Full text of body');
    $fields['body/summary'] = $this->t('Summary of body');
    $fields['field_image/target_id'] = $this->t('Image');
    $fields['field_media_image/target_id'] = $this->t('Media Image');
    return $fields;
  }
 
  // /**
  //  * {@inheritdoc}
  //  */
  public function prepareRow(Row $row) {
    $nid = $row->getSourceProperty('nid');
 
    // body (compound field with value, summary, and format)
    $result = $this->getDatabase()->query('
      SELECT
        fld.body_value,
        fld.body_summary,
        fld.body_format
      FROM
        {field_data_body} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      $row->setSourceProperty('body_value', $record->body_value );
      $row->setSourceProperty('body_summary', $record->body_summary );
      $row->setSourceProperty('body_format', $record->body_format );
    }
 
    // taxonomy term IDs
    // (here we use MySQL's GROUP_CONCAT() function to merge all values into one row.)
    $result = $this->getDatabase()->query('
      SELECT
        GROUP_CONCAT(fld.field_tags_tid) as tids
      FROM
        {field_data_field_tags} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      if (!is_null($record->tids)) {
        $row->setSourceProperty('tags', explode(',', $record->tids) );
      }
    }

    // Company taxonomy term IDs
    // (here we use MySQL's GROUP_CONCAT() function to merge all values into one row.)
    $result = $this->getDatabase()->query('
      SELECT
        GROUP_CONCAT(fld.field_company_companies_tid) as tids
      FROM
        {field_data_field_company_companies} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    foreach ($result as $record) {
      if (!is_null($record->tids)) {
        $row->setSourceProperty('company_tags', explode(',', $record->tids) );
      }
    }

    $result = $this->getDatabase()->query('
      SELECT
        fld.field_image_fid,
        fld.field_image_alt,
        fld.field_image_title,
        fld.field_image_width,
        fld.field_image_height
      FROM
        {field_data_field_image} fld
      WHERE
        fld.entity_id = :nid
    ', array(':nid' => $nid));
    // Create an associative array for each row in the result. The keys
    // here match the last part of the column name in the field table. 
     $image = [];

    foreach ($result as $record) {
      $image[] = [
        'target_id' => $record->field_image_fid,
        'alt' => $record->field_image_alt,
        'title' => $record->field_image_title,
        'width' => $record->field_image_width,
        'height' => $record->field_image_height,
      ];
    }
    $row->setSourceProperty('image', $image);
 
    return parent::prepareRow($row);
  }
 
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids['nid']['type'] = 'integer';
    $ids['nid']['alias'] = 'n';
    return $ids;
  }
 
  /**
   * {@inheritdoc}
   */
  public function bundleMigrationRequired() {
    return FALSE;
  }
 
  /**
   * {@inheritdoc}
   */
  public function entityTypeId() {
    return 'node';
  }
 
  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = array(
      'nid' => $this->t('Node ID'),
      'vid' => $this->t('Version ID'),
      'type' => $this->t('Type'),
      'title' => $this->t('Title'),
      'format' => $this->t('Format'),
      'teaser' => $this->t('Teaser'),
      'uid' => $this->t('Authored by (uid)'),
      'created' => $this->t('Created timestamp'),
      'changed' => $this->t('Modified timestamp'),
      'status' => $this->t('Published'),
      'promote' => $this->t('Promoted to front page'),
      'sticky' => $this->t('Sticky at top of lists'),
      'language' => $this->t('Language (fr, en, ...)'),
    );
    return $fields;
  }
}
?>