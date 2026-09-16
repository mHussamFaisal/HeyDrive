<?php
use App\Helpers\SiteHelper;

switch($action)
{
	case 'init':

		$typeList= array(
			array(
				'value' => 0,
				'text' => 'Flat'
			),
			array(
				'value' => 1,
				'text' => 'Percent'
			)
		);

		$data['typeList'] = $typeList;
		$data['success'] = true;

	break;
	case 'list':
						if (!auth()->user()->hasPermission('admin.discounts.index')) {
								return redirect_no_permission();
						}

		// Convert start
		$data['list'] = [];

		$searchData = $etoPost['search']['value'];
		parse_str($searchData, $output);
		// $data['outputData'] = $output;

		$etoPost['searchFilterCategory'] = json_encode(isset($output['filter-type']) ? $output['filter-type'] : []);
		$etoPost['searchText'] = isset($output['filter-keywords']) ? $output['filter-keywords'] : '';

		$sortData = array();
		if ( isset($etoPost['order']) ) {
			foreach($etoPost['order'] as $key => $value) {
				$index = (int)$value['column'];
				$new = new \stdClass();
				$new->property = $etoPost['columns'][$index]['data'];
				$new->direction = ($value['dir'] == 'asc') ? 'ASC' : 'DESC';
				$sortData[] = $new;
			}
		}
		$etoPost['sort'] = json_encode($sortData);
		// Convert end


		$sort    = json_decode($etoPost['sort']);
		$start   = (int) $etoPost['start'];
		$limit   = (int) $etoPost['length'];
		// $page    = (int) $etoPost['page'];
		$sqlSort = '';

		if (!empty($sort))
		{
			foreach($sort as $key => $value)
			{
				$property = '';

				switch((string) $value->property)
				{
					case 'id':
						$property = '`id`';
					break;
					case 'code':
						$property = '`code`';
					break;
					case 'type':
						$property = '`type`';
					break;
					case 'value':
						$property = '`value`';
					break;
					case 'allowed_times':
						$property = '`allowed_times`';
					break;
					case 'used_times':
						$property = '`used_times`';
					break;
					case 'start_date':
						$property = '`start_date`';
					break;
					case 'end_date':
						$property = '`end_date`';
					break;
					case 'minimum_bookings':
						$property = '`minimum_bookings`';
					break;
					case 'description':
						$property = '`description`';
					break;
					case 'created_date':
						$property = '`created_date`';
					break;
					case 'published':
						$property = '`published`';
					break;
				}

				if (!empty($property))
				{
					$sqlSort .= $property . ' ' . $value->direction . ', ';
				}
			}
		}

		// Search text
		$searchText = (string) $etoPost['searchText'];
		$searchText = SiteHelper::makeStrSafe($searchText);
		$sqlSearch  = '';

		if (!empty($searchText))
		{
			$sqlSearch .= " AND (`id` LIKE '%" . $searchText . "%'".
										" OR `code` LIKE '%" . $searchText . "%'".
										" OR `start_date` LIKE '%" . $searchText . "%'".
										" OR `end_date` LIKE '%" . $searchText . "%'".
										" OR `description` LIKE '%" . $searchText . "%'".
										" )";
		}


		// Search filter category
		$searchFilterCategory    = json_decode($etoPost['searchFilterCategory']);
		$sqlSearchFilterCategory = '';

		if (!empty($searchFilterCategory))
		{
			foreach($searchFilterCategory as $key => $value)
			{
				if (!empty($sqlSearchFilterCategory))
				{
					$sqlSearchFilterCategory .= ' OR ';
				}

				if (isset($value))
				{
					$sqlSearchFilterCategory .= '`type` LIKE \'%' . (string) $value . '%\'';
				}
			}

			if (!empty($sqlSearchFilterCategory))
			{
				$sqlSearchFilterCategory = ' AND (' . $sqlSearchFilterCategory . ') ';
			}
		}

		$sqlSearch .= " " . $sqlSearchFilterCategory;

		// $data['sqlSearchFilter'] = $sqlSearchFilter;


		// Total
		$sql = "SELECT `id`
						FROM `{$dbPrefix}discount`
						WHERE `site_id`='" . $siteId . "' ";

		$qDiscountTotal = count($db->select($sql));
		$data['recordsTotal'] = $qDiscountTotal;


		// Filtered
		$sql = "SELECT `id`
						FROM `{$dbPrefix}discount`
						WHERE `site_id`='" . $siteId . "' " . $sqlSearch;

		$qDiscountTotal = count($db->select($sql));
		$data['recordsFiltered'] = $qDiscountTotal;


		// List
		$sql = "SELECT *
						FROM `{$dbPrefix}discount`
						WHERE `site_id`='" . $siteId . "' " . $sqlSearch . "
						ORDER BY " . $sqlSort . " `code` ASC
						LIMIT {$start},{$limit}";

		$qDiscount = $db->select($sql);

		if ( !empty($qDiscount) )
		{
			$discount = array();

			foreach($qDiscount as $key => $value)
			{
				if ( $value->type == 1 ) {
					$type = 'Percent';
					$typeValue = $value->value .'%';
				}
				else {
					$type = 'Flat';
					$typeValue = config('site.currency_symbol') . $value->value . config('site.currency_code');
				}

				$columns = array();
				$columns['id'] = $value->id;
				$columns['code'] = $value->code;
				$columns['type'] = $type;
				$columns['value'] = $typeValue;
				$columns['allowed_times'] = $value->allowed_times;
				$columns['used_times'] = $value->used_times;
				$columns['start_date'] = SiteHelper::formatDateTime($value->start_date);
				$columns['end_date'] = SiteHelper::formatDateTime($value->end_date);
				$columns['minimum_bookings'] = $value->minimum_bookings;
				$columns['description'] = $value->description;
				$columns['created_date'] = SiteHelper::formatDateTime($value->created_date);
				$columns['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';

				$discount[] = $columns;
			}

			$data['list'] = $discount;
			$data['success'] = true;
		}
		else
		{
			$data['message'][] = 'No discounts were found!';
			$data['success'] = true;
		}

	break;
	case 'read':
						if (!auth()->user()->hasPermission('admin.discounts.show')) {
								return redirect_no_permission();
						}

		$id = (int) $etoPost['id'];

		$sql = "SELECT *
						FROM `{$dbPrefix}discount`
						WHERE `site_id`='" . $siteId . "'
						AND `id`='" . $id . "'
						LIMIT 1";

		$qDiscount = $db->select($sql);
		if (!empty($qDiscount[0])) {
			$qDiscount = $qDiscount[0];
		}

		if (!empty($qDiscount))
		{
			$record = array(
				'id' => $qDiscount->id,
				'site_id' => $qDiscount->site_id,
				'code' => $qDiscount->code,
				'type' => (int)$qDiscount->type,
				'value' => $qDiscount->value,
				'allowed_times' => $qDiscount->allowed_times,
				'used_times' => $qDiscount->used_times,
				'start_date' => $qDiscount->start_date ? date('Y-m-d H:i', strtotime($qDiscount->start_date) ) : '',
				'end_date' => $qDiscount->end_date ? date('Y-m-d H:i', strtotime($qDiscount->end_date) ) : '',
				'minimum_bookings' => $qDiscount->minimum_bookings,
				'description' => $qDiscount->description,
				'published' => $qDiscount->published
			);

			$data['record'] = $record;
			$data['success'] = true;
		}
		else
		{
			$data['message'][] = $gLanguage['API']['ERROR_NO_Discount'];
		}

	break;
	case 'update':
						if (!auth()->user()->hasPermission('admin.discounts.edit')) {
								return redirect_no_permission();
						}

		$id = (int)$etoPost['id'];
		$site_id = (int)$etoPost['site_id'];
		$code = (string)$etoPost['code'];
		$type = (int)$etoPost['type'];
		$value = (float)$etoPost['value'];
		$allowed_times = (int)$etoPost['allowed_times'];
		$used_times = (int)$etoPost['used_times'];
		$start_date = (string)$etoPost['start_date'];
		$end_date = (string)$etoPost['end_date'];
		$minimum_bookings = (int)$etoPost['minimum_bookings'];
		$description = (string)$etoPost['description'];
		$published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;

		$sql = "SELECT `id`
				FROM `{$dbPrefix}discount`
				WHERE `site_id`='" . $siteId . "'
				AND `id`='" . $id . "'
				LIMIT 1";

		$qDiscount = $db->select($sql);
		if (!empty($qDiscount[0])) {
			$qDiscount = $qDiscount[0];
		}

		$row = new \stdClass();
		$row->id = null;
		$row->site_id = ($site_id) ? $site_id : (int)$siteId;
		$row->code = trim($code);
		$row->type = $type;
		$row->value = $value;
		$row->allowed_times = $allowed_times;
		$row->used_times = $used_times;
		$row->start_date = $start_date ?: null;
		$row->end_date = $end_date ?: null;
		$row->minimum_bookings = $minimum_bookings;
		$row->description = $description;
		$row->published = $published;

		if ( !empty($qDiscount) ) // Update
		{
			$row->id = $qDiscount->id;

			$results = \DB::table('discount')->where('id', $row->id)->update((array)$row);
			$results = $row->id;
		}
		else // Insert
		{
			$row->created_date = date('Y-m-d H:i:s');

			$results = \DB::table('discount')->insertGetId((array)$row);
			$row->id = $results;
		}

		$data['success'] = true;

	break;
	case 'destroy':
						if (!auth()->user()->hasPermission('admin.discounts.destroy')) {
								return redirect_no_permission();
						}

		$id = (int) $etoPost['id'];

		$sql = "SELECT `id`
						FROM `{$dbPrefix}discount`
						WHERE `site_id`='" . $siteId . "'
						AND `id`='" . $id . "'
						LIMIT 1";

		$qDiscount = $db->select($sql);
		if (!empty($qDiscount[0])) {
			$qDiscount = $qDiscount[0];
		}

		if (!empty($qDiscount))
		{
			// Remove
			$sql = "DELETE FROM `{$dbPrefix}discount` WHERE `id`='" . $qDiscount->id . "' LIMIT 1";
			$results = $db->delete($sql);

			$data['success'] = true;
		}
		else
		{
			$data['message'][] = $gLanguage['API']['ERROR_NO_Discount'];
		}

	break;
}
