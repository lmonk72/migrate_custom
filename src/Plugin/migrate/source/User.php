<?php
/**
 * @file
 * Contains \Drupal\migrate_custom\Plugin\migrate\source\User.
 */
 
namespace Drupal\migrate_custom\Plugin\migrate\source;
 
use Drupal\migrate\Row;
use Drupal\migrate\Plugin\migrate\source\SqlBase;
 
/**
 * Extract users from Drupal 7 database.
 *
 * @MigrateSource(
 *   id = "custom_user"
 * )
 */
class User extends SqlBase {
 
  /**
   * {@inheritdoc}
   */
  public function query() {
    return $this->select('users', 'u')
      ->fields('u', array_keys($this->baseFields()))
      ->condition('uid', 0, '>');
  }
 
  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = $this->baseFields();
    $fields['user_first_name'] = $this->t('First Name');
    $fields['user_last_name'] = $this->t('Last Name');
    return $fields;
  }
 
  /**
   * {@inheritdoc}
   */
  public function prepareRow(Row $row) {
    $uid = $row->getSourceProperty('uid');
 
    /*
     * All records fetched are grabbed by an SQL query
     * Select the value (column name) from a table matching on the UID 
     * Set a property name e.g. user_first_name with the SQL query result e.g. field_user_first_name_value
     */

    // first_name
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_user_first_name_value
      FROM
        {field_data_field_user_first_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('user_first_name', $record->field_user_first_name_value );
    }
 
    // last_name
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_user_last_name_value
      FROM
        {field_data_field_user_last_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('user_last_name', $record->field_user_last_name_value );
    }

    // Job title
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_user_job_title_value
      FROM
        {field_data_field_user_job_title} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('job_title', $record->field_user_job_title_value );
    }

    // Contact number
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_user_contact_number_prime_value
      FROM
        {field_data_field_user_contact_number_prime} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('contact_number', $record->field_user_contact_number_prime_value );
    }

    // Company name
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_user_company_name_value
      FROM
        {field_data_field_user_company_name} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('company_name', $record->field_user_company_name_value );
    }

    // Company type
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_company_type_value
      FROM
        {field_data_field_company_type} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('company_type', $record->field_company_type_value );
    }

    // Company size
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_company_size_value
      FROM
        {field_data_field_company_size} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('company_size', $record->field_company_size_value );
    }

    // Profile Updated
    $result = $this->getDatabase()->query('
      SELECT
        fld.field_profile_last_updated_value
      FROM
        {field_data_field_profile_last_updated} fld
      WHERE
        fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('profile_updated', $record->field_profile_last_updated_value );
    }

    // Country
     $result = $this->getDatabase()->query('
     SELECT
       fld.field_user_country_value
     FROM
       {field_data_field_user_country} fld
     WHERE
       fld.entity_id = :uid
   ', array(':uid' => $uid));
   foreach ($result as $record) {
     $row->setSourceProperty('country_key', $record->field_user_country_value );
   }

   // Preferences - Newsletter mailing list
   $result = $this->getDatabase()->query('
    SELECT
      fld.field_pref_news_bulletin_value
    FROM
      {field_data_field_pref_news_bulletin} fld
    WHERE
      fld.entity_id = :uid
  ', array(':uid' => $uid));
  foreach ($result as $record) {
    $row->setSourceProperty('news_mailing_list', $record->field_pref_news_bulletin_value );
  }
  
   // Preferences - Events mailing list
   $result = $this->getDatabase()->query('
    SELECT
      fld.field_pref_event_updates_value
    FROM
      {field_data_field_pref_event_updates} fld
    WHERE
      fld.entity_id = :uid
  ', array(':uid' => $uid));
  foreach ($result as $record) {
    $row->setSourceProperty('event_mailing_list', $record->field_pref_event_updates_value );
  }

   // Preferences - Vendor initiatives mailing list
   $result = $this->getDatabase()->query('
    SELECT
      fld.field_pref_initiative_updates_value
    FROM
      {field_data_field_pref_initiative_updates} fld
    WHERE
      fld.entity_id = :uid
  ', array(':uid' => $uid));
  foreach ($result as $record) {
    $row->setSourceProperty('vendor_mailing_list', $record->field_pref_initiative_updates_value );
  }

   // Preferences - Research & Market Intelligence mailing list
   $result = $this->getDatabase()->query('
    SELECT
      fld.field_pref_reports_whitepapers_value
    FROM
      {field_data_field_pref_reports_whitepapers} fld
    WHERE
      fld.entity_id = :uid
    ', array(':uid' => $uid));
    foreach ($result as $record) {
      $row->setSourceProperty('market_intelligence_mailing_list', $record->field_pref_reports_whitepapers_value );
    }


    // Grabs Role ID and assigns
    $query = $this->select('users_roles', 'r');
    $query->fields('r', ['rid']);
    $query->condition('r.uid', $uid, '=');
    $record = $query->execute()->fetchAllKeyed();
    $row->setSourceProperty('roles', array_keys($record));
     
    return parent::prepareRow($row);
  }
 
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return array(
      'uid' => array(
        'type' => 'integer',
        'alias' => 'u',
      ),
    );
  }
 
  /**
   * Returns the user base fields to be migrated.
   *
   * @return array
   *   Associative array having field name as key and description as value.
   */
  protected function baseFields() {
    $fields = array(
      'uid' => $this->t('User ID'),
      'name' => $this->t('Username'),
      'pass' => $this->t('Password'),
      'mail' => $this->t('Email address'),
      'signature' => $this->t('Signature'),
      'signature_format' => $this->t('Signature format'),
      'created' => $this->t('Registered timestamp'),
      'access' => $this->t('Last access timestamp'),
      'login' => $this->t('Last login timestamp'),
      'status' => $this->t('Status'),
      'timezone' => $this->t('Timezone'),
      'language' => $this->t('Language'),
      'picture' => $this->t('Picture'),
      'init' => $this->t('Init'),
    );
    return $fields;
 
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
    return 'user';
  }
 
}
?>