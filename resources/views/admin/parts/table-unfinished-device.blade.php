{{ !empty($item->login->device) && $item->login->device != 'smartphone' ? ucfirst($item->login->device) : '' }}{{ !empty($item->login->brand) ? ', '.ucfirst($item->login->brand) : '' }}{{ !empty($item->login->model) ? (!empty($item->login->device) && $item->login->device != 'smartphone' ? ', ' : '').ucfirst($item->login->model) : '' }}{{ !empty($item->login->os) ? ', '.ucfirst($item->login->os) : '' }}