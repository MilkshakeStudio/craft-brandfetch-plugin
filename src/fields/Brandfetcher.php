<?php
/**
 * Brandfetch plugin for Craft CMS 3.x
 *
 * brandfetch api
 *
 * @link      https://milkshake.studio
 * @copyright Copyright (c) 2020 Milkshake Studio
 */

namespace milkshakestudio\brandfetch\fields;

use milkshakestudio\brandfetch\Brandfetch;
use milkshakestudio\brandfetch\assetbundles\brandfetcherfield\BrandfetcherFieldAsset;

use Craft;
use craft\base\ElementInterface;
use craft\base\Field;
use craft\helpers\Db;
use yii\db\Schema;
use craft\helpers\Json;


use craft\elements\Asset;

use function PHPSTORM_META\elementType;

/**
 * Brandfetcher Field
 *
 * Whenever someone creates a new field in Craft, they must specify what
 * type of field it is. The system comes with a handful of field types baked in,
 * and we’ve made it extremely easy for plugins to add new ones.
 *
 * https://craftcms.com/docs/plugins/field-types
 *
 * @author    Milkshake Studio
 * @package   Brandfetch
 * @since     0.0.1
 */
class Brandfetcher extends Field
{
    // Public Properties
    // =========================================================================

    /**
     * URL for BrandFetch
     *
     * @var string
     */

    public $url = 'https://google.com';

    // Static Methods
    // =========================================================================

    /**
     * Returns the display name of this class.
     *
     * @return string The display name of this class.
     */
    public static function displayName(): string
    {
        return Craft::t('brandfetch', 'Brandfetcher');
    }

    /**
     * Returns the column type that this field should get within the content table.
     *
     * This method will only be called if [[hasContentColumn()]] returns true.
     *
     * @return string The column type. [[\yii\db\QueryBuilder::getColumnType()]] will be called
     * to convert the give column type to the physical one. For example, `string` will be converted
     * as `varchar(255)` and `string(100)` becomes `varchar(100)`. `not null` will automatically be
     * appended as well.
     * @see \yii\db\QueryBuilder::getColumnType()
     */
    public function getContentColumnType(): string
    {
        return Schema::TYPE_STRING;
    }

    /**
     * Normalizes the field’s value for use.
     *
     * This method is called when the field’s value is first accessed from the element. For example, the first time
     * `entry.myFieldHandle` is called from a template, or right before [[getInputHtml()]] is called. Whatever
     * this method returns is what `entry.myFieldHandle` will likewise return, and what [[getInputHtml()]]’s and
     * [[serializeValue()]]’s $value arguments will be set to.
     *
     * @param mixed                 $value   The raw field value
     * @param ElementInterface|null $element The element the field is associated with, if there is one
     *
     * @return mixed The prepared field value
     */
    public function normalizeValue($value, ElementInterface $element = null)
    {
        // get the logo if available
        if($value){
            $asset = Asset::find()->id($value)->one();
            return parent::normalizeValue($asset, $element);
        }else{
            return parent::normalizeValue($value, $element);
        }
        
    }

    /**
     * Modifies an element query.
     *
     * This method will be called whenever elements are being searched for that may have this field assigned to them.
     *
     * If the method returns `false`, the query will be stopped before it ever gets a chance to execute.
     *
     * @param ElementQueryInterface $query The element query
     * @param mixed                 $value The value that was set on this field’s corresponding [[ElementCriteriaModel]] param,
     *                                     if any.
     *
     * @return null|false `false` in the event that the method is sure that no elements are going to be found.
     */
    public function serializeValue($value, ElementInterface $element = null)
    {
        if(!empty($value)){
            // $asset = Asset::find()->id($value)->one();
            return parent::normalizeValue($value->id, $element);
        }else{
            return parent::normalizeValue($value, $element);
        }
        
        // return $value->id;
    }

    /**
     * Returns the component’s settings HTML.
     * @return string|null
     */
    public function getSettingsHtml()
    {        
        // Render the settings template
        return Craft::$app->getView()->renderTemplate(
            'brandfetch/_components/fields/Brandfetcher_settings',
            [
                'field' => $this
            ]
        );
    }

    /**
     * Returns the field’s input HTML.
     * @param mixed                 $value           The field’s value. This will either be the [[normalizeValue() normalized value]],
     *                                               raw POST data (i.e. if there was a validation error), or null
     * @param ElementInterface|null $element         The element the field is associated with, if there is one
     *
     * @return string The input HTML.
     */
    public function getInputHtml($value, ElementInterface $element = null): string
    {
        // Register our asset bundle
        Craft::$app->getView()->registerAssetBundle(BrandfetcherFieldAsset::class);

        // Get our id and namespace
        $id = Craft::$app->getView()->formatInputId($this->handle);
        $namespacedId = Craft::$app->getView()->namespaceInputId($id);
        
        // // get the logo if available
        if( gettype($value) === 'object' ){
            $logoURL = Craft::$app->getAssets()->getThumbUrl($value, 300, 200, false, false);
        }
        
        
        // TODO: this should be the same as the varriabls that go to the field template
        // Variables to pass down to our field JavaScript to let it namespace properly
        $jsonVars = [
            'id' => $id,
            'name' => $this->handle,
            'namespace' => $namespacedId,
            'prefix' => Craft::$app->getView()->namespaceInputId(''),
            'elementType' =>   'craft\\elements\\Asset',
            ];
        $jsonVars = Json::encode($jsonVars);
        Craft::$app->getView()->registerJs("$('#{$namespacedId}-field').BrandfetchBrandfetcher(" . $jsonVars . ");");
        
        // Is API SET?
        $apiSet = !empty(Brandfetch::$plugin->getSettings()->brandfetch_api_key);

        // Render the input template
        return Craft::$app->getView()->renderTemplate(
            'brandfetch/_components/fields/Brandfetcher_input',
            [
                'name' => $this->handle,
                'value' => $this->serializeValue($value),
                'test'=> $value,
                'field' => $this,
                'id' => $id,
                'logo' => $logoURL ?? null,
                'url' => $this->url,
                'apiSet' => $apiSet,
                'namespacedId' => $namespacedId,
                'elementType' =>  'craft\\elements\\Asset',
            ]
        );
    }
}
