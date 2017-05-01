<?php


namespace ModelRenderers\DataTable;


use ModelRenderers\DataTable\Util\DataTableRendererUtil;
use ModelRenderers\Renderer\AbstractRenderer;

/**
 * This renderer works with a companion js script, which breathe life into
 * the rendered html static code.
 *
 *
 * Both the js and the renderer work together, united by the following convention.
 *
 * Css classes to attribute:
 *
 *
 * - actionbutton-button: to an actionbutton button
 *                  The data-id attribute is used to retrieve the actionbutton identifier.
 *                  All attributes of the link, except the label and the icon,
 *                  are available as data-* attributes.
 *
 * - nipp-selector: to the nipp selector.
 *                  The value is retrieved using regular form value extraction technique for select.
 * - quickpage-input: to the quick page input
 *                  The value is retrieved using regular form value extraction technique for input.
 * - quickpage-button: to the quick page button
 * - checkboxes-toggler: to the top checkbox used to toggle the others.
 *                  The value is retrieved using regular form value extraction technique for checkbox.
 * - sort-item: on an element that triggers sort update.
 *              The sort update follows a cycle:
 *                  - no sort
 *                  - asc
 *                  - desc
 *
 *              The sort-item also has an extra class indicating the
 *              current state it is in:
 *                  - sort-nosort
 *                  - sort-asc
 *                  - sort-desc
 *
 *              Also, the data-id attribute is used to retrieve the columnId
 * - search-input: a search input for a given column.
 *              The value is retrieved using regular form value extraction technique for input.
 *              Also, the data-id attribute is used to retrieve the columnId
 * - search-button: the search button
 *
 * - ric-checkbox: the checkbox of a row (holding the ric string value).
 *                      The data-id attribute is used to hold the ric string value.
 * - bulk-selector: the selector for the bulk actions.
 *                  The value of the option is the action identifier.
 *                  The value is retrieved using regular form value extraction technique for select.
 * - pagination-link: a pagination link.
 *                  The value is retrieved using the value of the data-id attribute
 *
 *
 * - special-link: a link created using the special features of the row of type link.
 *                      All attributes of the link, except the label and the icon,
 *                      are available as data-* attributes.
 *
 *
 *
 * - data-store: This is a special hidden div used only for the purpose of transmitting
 *                  data to the js script.
 *                  The problem in the first place was this.
 *
 *                  Imagine for a second that you are the script,
 *                  and so you need to refresh the datatable view.
 *                  One function that you will have is therefore the refresh function.
 *                  The refresh function needs to send all parameters necessary
 *                  to display the datatable correctly, consistently with
 *                  the user gui.
 *                  In fact, there are only four parameters that need to be sent:
 *                  page, nipp, sortValues and searchValues.
 *
 *                  Ok, how would you send page for instance?
 *                  -> probably guessing it from the quickPage selector, right?
 *                  But what if it's not displayed?
 *                  -> probably fallback on the pagination links, right?
 *                  But what if it's not displayed?
 *                  -> ... that doesn't work, we need another system.
 *
 *
 *                  Also, what if the developer uses showSort=false,
 *                  but at the same time wants the rows to be ordered
 *                  by id desc?
 *                  From where would you retrieve the sort information then?
 *
 *
 *
 *                  That's right, we need a more consistent system.
 *                  So datastore basically stores all four data in its
 *                  data-* attributes:
 *
 *                  - data-ric: a comma separated list of ric items
 *                  - data-columns: a comma separated list of columnId
 *                  - data-page
 *                  - data-nipp
 *                  - data-sort-$columnId: $sortDir
 *                          There is one attribute of this kind for every available column.
 *                          With sortDir being one of: asc, desc, none.
 *                  - data-search-$columnId: $searchValue
 *                          There is one attribute of this kind for every available column.
 *
 *
 */
class DataTableRendererCopys extends AbstractRenderer
{


    public static function create()
    {
        return new static();
    }


    public function render()
    {
        ob_start();
        $a = $this->model;
//        a($a);

        $visibleColumns = $a['headers'];
        foreach ($a['hidden'] as $columnId) {
            unset($visibleColumns[$columnId]);
        }


        $nbVisibleColumns = count($visibleColumns);
        if (true === $a['isSearchable']) {
            $nbVisibleColumns++;
        }
        if (true === $a['checkboxes']) {
            $nbVisibleColumns++;
        }

        $storeAttr = DataTableRendererUtil::getStoreAttributes($a);
        ?>
        <div class="datatable_wrapper">
            <div
                <?php echo DataTableRendererUtil::toDataAttributes($storeAttr); ?>
                    class="data-store" style="display: none"></div>
            <div class="actionbuttons-bar">
                <?php if (true === $a['showActionButtons']): ?>
                    <?php foreach ($a['actionButtons'] as $id => $actionButton): ?>
                        <button class="actionbutton-button"
                            <?php echo DataTableRendererUtil::toDataAttributes($actionButton); ?>
                                data-id="<?php echo $id; ?>"><?php echo $actionButton['label']; ?></button>
                    <?php endforeach ?>
                <?php endif; ?>
            </div>
            <div class="top-bar">
                <?php if (true === $a['showNipp']): ?>
                    <div>
                        <?php echo DataTableRendererUtil::getNippSelector($a, ['attr' => ['class' => "nipp-selector"]]); ?>
                    </div>
                <?php endif; ?>
                <?php if (true === $a['showQuickPage']): ?>
                    <div>
                        <?php echo $a['textQuickPage']; ?>
                        <input class="quickpage-input" type="text" value="<?php echo $a['page']; ?>">
                        <button class="quickpage-button"><?php echo $a['textQuickPageButton']; ?></button>
                    </div>
                <?php endif; ?>
            </div>
            <table>
                <thead>
                <tr>
                    <?php if (true === $a['checkboxes']): ?>
                        <th><input type="checkbox" class="checkboxes-toggler"></th>
                    <?php endif; ?>


                    <?php foreach ($visibleColumns as $columnId => $label): ?>
                        <th>
                            <?php if (true === $a['isSortable'] && false === in_array($columnId, $a['unsortable'], true)): ?>

                                <?php
                                $class = 'sort-item';
                                $dir = "nosort";
                                if (array_key_exists($columnId, $a['sortValues'])) {
                                    $dir = $a["sortValues"][$columnId];
                                }
                                $class .= ' sort-' . $dir;
                                ?>
                                <a data-id="<?php echo $columnId; ?>" href="#" class="<?php echo $class; ?>">
                                    <?php echo $label; ?>
                                </a>
                            <?php else: ?>
                                <?php echo $label; ?>
                            <?php endif; ?>
                        </th>
                    <?php endforeach; ?>

                    <?php if (true === $a['isSearchable']): ?>
                        <th></th>
                    <?php endif; ?>
                </tr>
                <?php if (true === $a['isSearchable']): ?>
                    <tr class="search_row">
                        <?php if (true === $a['checkboxes']): ?>
                            <td></td>
                        <?php endif; ?>
                        <?php foreach ($visibleColumns as $columnId => $label): ?>
                            <td>
                                <?php if (false === in_array($columnId, $a['unsearchable'])): ?>
                                    <?php
                                    $val = (array_key_exists($columnId, $a['searchValues'])) ? $a['searchValues'][$columnId] : "";
                                    ?>
                                    <input data-id="<?php echo $columnId; ?>" class="search-input" type="text"
                                           value="<?php echo htmlspecialchars($val); ?>">
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                        <td>
                            <button class="search-button"><?php echo $a['textSearch']; ?></button>
                        </td>
                    </tr>
                <?php endif; ?>
                </thead>
                <tbody>
                <?php if (count($a['rows']) > 0): ?>
                    <?php foreach ($a['rows'] as $row): ?>
                        <tr>

                            <?php if (true === $a['checkboxes']):
                                $ricString = DataTableRendererUtil::getRicValueStringByRow($a['ric'], $row);
                                ?>
                                <td><input class="ric-checkbox" type="checkbox" data-id="<?php echo $ricString; ?>">
                                </td>
                            <?php endif; ?>

                            <?php foreach ($row as $k => $v): ?>
                                <?php if (array_key_exists($k, $visibleColumns)): ?>
                                    <?php if (is_array($v)): ?>
                                        <td><?php echo $this->renderRowSpecial($v, $row); ?></td>
                                    <?php else: ?>
                                        <td><?php echo $v; ?></td>
                                    <?php endif; ?>
                                <?php endif; ?>
                            <?php endforeach ?>


                            <?php if (true === $a['isSearchable']): ?>
                                <td></td>
                            <?php endif; ?>


                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr class="message">
                        <td colspan="<?php echo $nbVisibleColumns; ?>"><?php echo $a['textNoResult']; ?></td>
                    </tr>
                <?php endif; ?>


                </tbody>
            </table>

            <?php if (true === $a['showBulkActions']): ?>
                <div class="bulk-bar">
                    <select class="bulk-selector">
                        <option value="0"><?php echo $a['textBulkActionsTeaser']; ?></option>
                        <?php foreach ($a['bulkActions'] as $identifier => $action): ?>
                            <option value="<?php echo $identifier; ?>"><?php echo $action['label']; ?></option>
                        <?php endforeach ?>
                    </select>
                </div>
            <?php endif; ?>
            <div class="bottom-bar">
                <?php if (true === $a['showCountInfo']): ?>
                    <div>
                        <?php echo DataTableRendererUtil::getCountInfoText($a); ?>
                    </div>
                <?php endif; ?>
                <?php if (true === $a['showPagination']): ?>
                    <div class="pagination">
                        <?php echo $this->renderPagination($a); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        <?php

        return ob_get_clean();
    }

    //--------------------------------------------
    //
    //--------------------------------------------
    protected function renderPagination(array $model)
    {
        $s = '';
        $nbPages = ceil($model['nbTotalItems'] / $model['nipp']);
        for ($i = 1; $i <= $nbPages; $i++) {
            $class = ($i === $model['page']) ? 'selected' : "";
            $s .= '<a class="pagination-link ' . $class . '" href="#">' . $i . '</a>';
        }
        return $s;
    }

    protected function renderRowSpecial(array $special, array $row)
    {
        $s = '';
        $type = $special['type'];
        $data = $special['data'];

        switch ($type) {
            case 'link':
                $s .= $this->renderLink($data);
                break;
            case 'links':
                foreach ($data as $oneData) {
                    $s .= $this->renderLink($oneData);
                }
                break;
            default:
                $this->onError("Unknown special type: $type");
                break;
        }
        return $s;
    }

    protected function renderLink(array $data)
    {
        $label = (array_key_exists('label', $data)) ? $data['label'] : "";
        $icon = (array_key_exists('icon', $data)) ? $data['icon'] : "";
        return '<button class="special-link" ' . DataTableRendererUtil::toDataAttributes($data) . '>' . $label . '</button>';
    }

    protected function onError($msg)
    {
        throw new \Exception("DataTableRenderer error: " . $msg);
    }
}
