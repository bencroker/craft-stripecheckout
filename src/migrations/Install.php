<?php
/**
 * Stripe Checkout plugin for Craft CMS 3.x
 *
 * Bringing the power of Stripe Checkout to your Craft templates.
 *
 * @link      https://github.com/jalendport/craft-stripecheckout
 * @copyright Copyright (c) 2018 Jalen Davenport
 */

namespace jalendport\stripecheckout\migrations;

use jalendport\stripecheckout\Support;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;
use craft\helpers\MigrationHelper;

class Install extends Migration
{
    // Public Properties
    // =========================================================================

    public $driver;

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        if ($this->createTables()) {
            $this->addForeignKeys();
            // Refresh the db schema caches
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;
        $this->dropForeignKeys();
        $this->dropTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;

        // support_tickets table
        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%checkout_charges}}');
        if ($tableSchema === null) {
            $tablesCreated = true;

            $this->createTable(
                '{{%checkout_charges}}',
                [
                    'id'             => $this->primaryKey(),
                    'dateCreated'    => $this->dateTime()->notNull(),
                    'dateUpdated'    => $this->dateTime()->notNull(),
                    'uid'            => $this->uid(),
                    // Custom columns in the table
                    'stripeId'       => $this->string(),
                    'email'          => $this->string(),
                    'live'           => $this->boolean(),
                    'chargeStatus'   => $this->string(),
                    'paid'           => $this->boolean(),
                    'refunded'       => $this->boolean(),
                    'amount'         => $this->integer(),
                    'amountRefunded' => $this->integer(),
                    'currency'       => $this->string(),
                    'description'    => $this->string(),
                    'failureCode'    => $this->string(),
                    'failureMessage' => $this->string(),
                    'data'           => $this->text(),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(null, '{{%checkout_charges}}', ['id'], '{{%elements}}', ['id'], 'CASCADE');
    }

    protected function dropForeignKeys()
    {
        MigrationHelper::dropAllForeignKeysOnTable('{{%checkout_charges}}', $this);
    }

    protected function dropTables()
    {
        $this->dropTable('{{%checkout_charges}}');
    }
}
