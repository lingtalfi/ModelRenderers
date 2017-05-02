<?php


namespace ModelRenderers\DataTable;


use ModelRenderers\DataTable\Util\DataTableRendererUtil;
use ModelRenderers\Renderer\AbstractRenderer;

class DataTableRenderer extends AbstractRenderer
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
                    <?php foreach ($a['actionButtons'] as $id => $actionButton):
                        if (array_key_exists("useSelectedRows", $actionButton) && true === $actionButton['useSelectedRows']) {
                            $actionButton['textUseSelectedRowsEmptyWarning'] = $a['textUseSelectedRowsEmptyWarning'];
                        }
                        ?>
                        <button class="actionbutton-button"
                            <?php echo DataTableRendererUtil::toDataAttributes($actionButton); ?>
                                data-id="<?php echo $id; ?>">
                            <?php if (array_key_exists('icon', $actionButton)): ?>
                                <i><?php echo $actionButton['icon']; ?></i>
                            <?php endif; ?>
                            <?php echo $actionButton['label']; ?>
                        </button>
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
                            <button class="search-clear-button"><?php echo $a['textSearchClear']; ?></button>
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

            <?php if (true === $a['showBulkActions']):
                $args = [
                    'show' => $a['showEmptyBulkWarning'],
                    'warning' => $a['textEmptyBulkWarning'],
                ];
                ?>
                <div class="bulk-bar">
                    <select
                        <?php echo DataTableRendererUtil::toDataAttributes($args); ?>
                            class="bulk-selector">
                        <option value="0"><?php echo $a['textBulkActionsTeaser']; ?></option>
                        <?php foreach ($a['bulkActions'] as $identifier => $action): ?>
                            <option data-id="<?php echo $identifier; ?>" <?php echo DataTableRendererUtil::toDataAttributes($action); ?>
                                    value="<?php echo $identifier; ?>"><?php echo $action['label']; ?></option>
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
        if ('all' !== $model['nipp']) {
            $nbPages = ceil($model['nbTotalItems'] / $model['nipp']);
        } else {
            $nbPages = 1;
        }
        for ($i = 1; $i <= $nbPages; $i++) {
            $class = ((int)$i === (int)$model['page']) ? 'selected' : "";
            $s .= '<a data-id="' . $i . '" class="pagination-link ' . $class . '" href="#">' . $i . '</a>';
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
        $s = '<button class="special-link" ' . DataTableRendererUtil::toDataAttributes($data) . '>';
        $s .= $icon . " ";
        $s .= $label;
        $s .= '</button>';
        return $s;
    }

    protected function onError($msg)
    {
        throw new \Exception("DataTableRenderer error: " . $msg);
    }
}
