<?php
use App\Helpers\SiteHelper;

switch( $action ) {
	case 'init':

		// Images
		$imagesList = array();
		$fileList = glob(asset_path('images','payments/*.{jpg,jpeg,gif,png}'), GLOB_BRACE);
		foreach($fileList as $filename) {
				$image = basename($filename);
				$text = ucwords(str_replace('_', ' ', $image));
				$text = substr($text, 0, (strrpos($text, '.')));
				$imagesList[] = array(
						'value' => $image,
						'text' => $text
				);
		}

		// Services
		$services = \App\Models\Service::where('relation_type', 'site')
			->where('relation_id', $siteId)
			->where('status', 'active')
			->orderBy('order', 'asc')
			->orderBy('name', 'asc')
			->get();

		$servicesList = [];
		foreach ($services as $key => $value) {
				$servicesList[] = [
						'id' => $value->id,
						'name' => $value->name
				];
		}

		$data['servicesList'] = $servicesList;
		$data['imagesList'] = $imagesList;
		$data['success'] = true;

	break;
	case 'list':
            if (!auth()->user()->hasPermission('admin.payments.index')) {
                return redirect_no_permission();
            }

		// Convert start
		$data['list'] = [];

		$searchData = $etoPost['search']['value'];
		parse_str($searchData, $output);
		// $data['outputData'] = $output;

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


		$sqlSort = '';
		$sqlFilter  = '';

		// Sort and limit
		$sort = json_decode($etoPost['sort']);
		$start = (int) $etoPost['start'];
		$limit = (int) $etoPost['length'];
		//$page = (int) $etoPost['page'];

		if ( !empty($sort) ) {
			foreach($sort as $key => $value) {
				$property = '';

				switch( (string)$value->property ) {
					case 'id':
						$property = '`a`.`id`';
					break;
					case 'service_ids':
						$property = '`a`.`service_ids`';
					break;
					case 'name':
						$property = '`a`.`name`';
					break;
					case 'description':
						$property = '`a`.`description`';
					break;
					case 'payment_page':
						$property = '`a`.`payment_page`';
					break;
					case 'image':
						$property = '`a`.`image`';
					break;
					case 'params':
						$property = '`a`.`params`';
					break;
					case 'method':
						$property = '`a`.`method`';
					break;
					case 'factor_type':
						$property = '`a`.`factor_type`';
					break;
					case 'price':
						$property = '`a`.`price`';
					break;
					case 'default':
						$property = '`a`.`default`';
					break;
					case 'ordering':
						$property = '`a`.`ordering`';
					break;
					case 'published':
						$property = '`a`.`published`';
					break;
					case 'is_backend':
						$property = '`a`.`is_backend`';
					break;
				}

				if ( !empty($property) ) {
					$sqlSort .= $property .' '. $value->direction .', ';
				}
			}
		}

		// Filters
		$text = (string)$etoPost['searchText'];
		$text = SiteHelper::makeStrSafe($text);

		if ( !empty($text) ) {
			$sqlFilter .= " AND (".
										" `a`.`name` LIKE '%". $text ."%'".
										" OR `a`.`image` LIKE '%". $text ."%'".
										" OR `a`.`description` LIKE '%". $text ."%'".
										" )";
		}

		// Total
		$sql = "SELECT `a`.*
						FROM `{$dbPrefix}payment` AS `a`
						WHERE `a`.`site_id`='". $siteId ."' ";

		$queryTotal = count($db->select($sql));
		$data['recordsTotal'] = $queryTotal;


		// Filtered
		$sql = "SELECT `a`.*
						FROM `{$dbPrefix}payment` AS `a`
						WHERE `a`.`site_id`='". $siteId ."' ". $sqlFilter;

		$queryTotal = count($db->select($sql));
		$data['recordsFiltered'] = $queryTotal;


		// List
		$sql = "SELECT `a`.*
						FROM `{$dbPrefix}payment` AS `a`
						WHERE `a`.`site_id`='". $siteId ."' ". $sqlFilter ."
						ORDER BY " . $sqlSort . " `name` ASC
						LIMIT {$start},{$limit}";

		$queryList = $db->select($sql);

		if ( !empty($queryList) ) {
			// Services
			$services = \App\Models\Service::where('relation_type', 'site')
				->where('relation_id', $siteId)
				->orderBy('order', 'asc')
				->orderBy('name', 'asc')
				->get();

			$rows = array();

			foreach($queryList as $key => $value) {
				$factor_type = '';
				$price = '';

				if ( $value->price ) {
					if ( !empty($value->factor_type) ) {
						$factor_type = '%';
						$price = $value->price . $factor_type;
					}
					else {
						$factor_type = '+';
						$price = $factor_type . $value->price;
					}
				}

				$image = '';
				if ( !empty($value->image) && \Storage::disk('payments')->exists($value->image) ) {
						$image = '<div style="width:100px;"><img src="'. asset_url('uploads','payments/'. $value->image) .'" class="paymentImage" /></div>';
				}
				else {
						// $image = '<div style="width:100px;"><img src="'. asset_url('images','placeholders/payment.png') .'" class="paymentImage" /></div>';
				}

				$serviceNames = '';
				if ( !empty($value->service_ids) ) {
					$service_ids = json_decode($value->service_ids);
					$i = 0;
					foreach ($services as $k => $v) {
						if ( in_array($v->id, $service_ids) ) {
							$serviceNames .= '<div>';
							$serviceNames .= $v->name;
							if ( $i < count($service_ids) - 1 ) {
								$serviceNames .= ', ';
								$i++;
							}
							$serviceNames .= '</div>';
						}
					}
				}
				else {
					$serviceNames = 'All';
				}

				$row = array();
				$row['id'] = $value->id;
				$row['name'] = $value->name;
				$row['description'] = $value->description;
				//$row['payment_page'] = $value->payment_page;
				$row['image'] = $image;
				$row['service_ids'] = $serviceNames;
				//$row['params'] = $value->params;
				$row['method'] = $value->method;
				$row['factor_type'] = $factor_type;
				$row['price'] = $price;
				$row['default'] = !empty($value->default) ? '<span style="color:green;">Yes</span>' : '';
				$row['ordering'] = $value->ordering;
				$row['published'] = !empty($value->published) ? '<span style="color:green;">Yes</span>' : '<span style="color:red;">No</span>';
				$row['is_backend'] = !empty($value->is_backend) ? '<span>Backend</span>' : '<span>Frontend & Backend</span>';

				$rows[] = $row;
			}

			$data['list'] = $rows;
			$data['success'] = true;
		}
		else {
			$data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENTS'];
			$data['success'] = true;
		}

	break;
	case 'read':
            if (!auth()->user()->hasPermission('admin.payments.show')) {
                return redirect_no_permission();
            }

		$id = (int)$etoPost['id'];

		$sql = "SELECT `a`.*
						FROM `{$dbPrefix}payment` AS `a`
						WHERE `a`.`site_id`='". $siteId ."'
						AND `a`.`id`='". $id ."'";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {

			// Vehicles
			$vehicleList = array();

			$sql = "SELECT `id`, `name`
							FROM `{$dbPrefix}vehicle`
							WHERE `site_id`='". $siteId ."'
							ORDER BY `ordering` ASC";
			$query2 = $db->select($sql);

			if ( !empty($query2) ) {
				foreach($query2 as $k => $v) {
					$vehicleList[] = array(
						'id' => (int)$v->id,
						'name' => (string)$v->name,
					);
				}
			}

			if ( !empty($query->service_ids) ) {
					$service_ids = json_decode($query->service_ids);
			}
			else {
					$service_ids = [];
			}

			$row = array(
				'id' => $query->id,
				'site_id' => $query->site_id,
				'name' => $query->name,
				'description' => $query->description,
				'service_ids' => $service_ids,
				'payment_page' => $query->payment_page,
				'image' => $query->image,
				'params' => $query->params,
				'method' => $query->method,
				'factor_type' => $query->factor_type,
				'price' => $query->price,
				'default' => $query->default,
				'ordering' => $query->ordering,
				'published' => $query->published,
				'is_backend' => $query->is_backend,
				'vehicle_list' => $vehicleList
			);

			$data['record'] = $row;
			$data['success'] = true;
		}
		else {
			$data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
		}

	break;
	case 'update':
            if (!auth()->user()->hasPermission('admin.payments.edit')) {
                return redirect_no_permission();
            }

			$request = request();

			$validator = \Validator::make($request->all(), [
					'image_upload' => 'mimes:jpg,jpeg,gif,png',
			]);

			$success = false;
			$errors = [];

			if ( $validator->fails() ) {
					if ( $validator->errors() ) {
							$errors[] = $validator->errors()->first();
					}
					$success = false;
			}
			else {
					if ( !empty($etoPost['service_ids']) ) {
							$service_ids = json_encode((array)$etoPost['service_ids']);
					}
					else {
							$service_ids = null;
					}

					$id = (int)$etoPost['id'];
					$site_id = (int)$etoPost['site_id'];
					$name = (string)$etoPost['name'];
					$description = (string)$etoPost['description'];
					$payment_page = (string)$etoPost['payment_page'];
					$image = (string)$etoPost['image'];
					$method = (string)$etoPost['method'];
					$factor_type = (int)$etoPost['factor_type'];
					$price = (float)$etoPost['price'];
					$default = ( (string)$etoPost['default'] == '1' ) ? 1 : 0;
					$ordering = (int)$etoPost['ordering'];
					$published = ( (string)$etoPost['published'] == '1' ) ? 1 : 0;
					$is_backend = (int)$etoPost['is_backend'];

					$params = (string)$etoPost['params'];
					$param_fields = $etoPost['param_fields'] ? (object)$etoPost['param_fields'] : (object)[];

					foreach ($param_fields as $kP => $vP) {
							if (in_array($kP, [
									// Epdq
									'pspid',
									'pass_phrase',
									'paramvar',
									// Cardsave | Payzone
									'pre_shared_key',
									'merchant_id',
									'password',
									// PayPal
									'paypal_email',
									// Redsys
									'terminal_id',
									'encryption_key',
									'signature_version',
									// Square
									'live_access_token',
									'live_location_id',
									'test_access_token',
									'test_location_id',
								  // Stripe | iDEAL | Worldpay Online Payments (wpop)
									'pk_live',
									'pk_test',
									'sk_live',
									'sk_test',
									// Worldpay
									'inst_id',
									'md5_secret',
									'signature_fields',
							])) {
									$param_fields->{$kP} = trim($vP);
							}
					}

					if ($method == 'stripe') {
							$scaMode = !empty((int)$param_fields->sca_mode) ? true : false;
							$testMode = !empty((int)$param_fields->test_mode) ? true : false;
							$secretKey = $testMode ? $param_fields->sk_test : $param_fields->sk_live;
							$webhookId = $testMode ? $param_fields->test_webhook_id : $param_fields->live_webhook_id;
							$webhookSecret = $testMode ? $param_fields->test_webhook_secret : $param_fields->live_webhook_secret;
							$webhookEnable = !empty($testMode ? (int)$param_fields->test_enable_webhook : (int)$param_fields->live_enable_webhook) ? true : false;
							$webhookObject = false;

							if (!$scaMode) {
									$webhookEnable = false;
							}

							if (!empty($secretKey) && !empty($webhookId)) {
									try {
											\Stripe\Stripe::setApiKey($secretKey);
											$webhookObject = \Stripe\WebhookEndpoint::retrieve($webhookId);

											if ($webhookEnable === false) {
													$webhookObject->delete();
											}
									}
									catch (\Exception $e) {
											\Log::error('Stripe '. ($testMode ? 'test' : 'live') .' webhook retrieve error: '. $e->getMessage());
									}
							}

							if ($webhookEnable === false) {
									$webhookId = '';
									$webhookSecret = '';
									$webhookObject = false;
							}

							if (!empty($secretKey) && empty($webhookId) && $webhookEnable === true && $scaMode) {
									try {
											\Stripe\Stripe::setApiKey($secretKey);

											$webhookParams = [
													'url' => url('/', [], $testMode ? \Request::isSecure() : true) .'/etov2?apiType=frontend&task=notify&webhook=stripe',
													'enabled_events' => ['checkout.session.completed']
											];

											if ($webhookObject === false) {
													$endpoint = \Stripe\WebhookEndpoint::create($webhookParams);
													// \Log::info('Stripe webhook created');
													// \Log::info($endpoint);
											}
											else {
													$webhookChanged = false;
													foreach ($webhookParams as $k => $v) {
															if ($v != $webhookObject[$k]) {
																	$webhookChanged = true;
															}
													}

													if ($webhookChanged === true) {
															$endpoint = \Stripe\WebhookEndpoint::update($webhookId, $webhookParams);
															// \Log::info('Stripe webhook updated');
															// \Log::info($endpoint);
													}

													// \Log::info('Stripe webhook changed: '. ($webhookChanged ? 'true' : 'false'));
											}

											if (!empty($endpoint->object) && $endpoint->object == 'webhook_endpoint') {
													if (!empty($endpoint->id)) {
															$webhookId = $endpoint->id;
													}
													if (!empty($endpoint->secret)) {
															$webhookSecret = $endpoint->secret;
													}
											}
									}
									catch (\Exception $e) {
											\Log::error('Stripe '. ($testMode ? 'test' : 'live') .' webhook '. ($webhookObject === false ? 'create' : 'update') .' error: '. $e->getMessage());
									}
							}

							$webhookId = !empty($webhookId) ? $webhookId : '';
							$webhookSecret = !empty($webhookSecret) ? $webhookSecret : '';

							if ($testMode) {
									$param_fields->test_webhook_id = $webhookId;
									$param_fields->test_webhook_secret = $webhookSecret;
							}
							else {
									$param_fields->live_webhook_id = $webhookId;
									$param_fields->live_webhook_secret = $webhookSecret;
							}
					}

					$param_fields = json_encode($param_fields);
					if ( !empty($param_fields) ) {
							$params = $param_fields;
					}

					$sql = "SELECT `id`, `image`
									FROM `{$dbPrefix}payment`
									WHERE `site_id`='". $siteId ."'
									AND `id`='" . $id . "'
									LIMIT 1";

					$queryPayment = $db->select($sql);
					if (!empty($queryPayment[0])) {
							$queryPayment = $queryPayment[0];
					}

					$delete = 0;
					$image = '';

					if ( !empty($queryPayment->image) ) {
							$image = $queryPayment->image;
					}

					$prevImage = $image;

					if ( $request->hasFile('image_upload') ) {
							$file = $request->file('image_upload');
							$filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. $file->getClientOriginalExtension();

							$img = \Image::make($file);

							if ($img->width() > config('site.image_dimensions.payment.width')) {
	                $img->resize(config('site.image_dimensions.payment.width'), null, function ($constraint) {
	                    $constraint->aspectRatio();
	                    $constraint->upsize();
	                });
	            }

	            if ($img->height() > config('site.image_dimensions.payment.height')) {
	                $img->resize(null, config('site.image_dimensions.payment.height'), function ($constraint) {
	                    $constraint->aspectRatio();
	                    $constraint->upsize();
	                });
	            }

							$img->save(asset_path('uploads','payments/'. $filename));

							if ( \Storage::disk('payments')->exists($filename) ) {
									$image = $filename;
									$delete = 1;
							}
					}
					elseif ( !empty($request->get('image_gallery')) && \Storage::disk('images-payments')->exists($request->get('image_gallery')) ) {
							$filepath = asset_path('images','payments/'. $request->get('image_gallery'));
							$filename = \App\Helpers\SiteHelper::generateFilename('payment') .'.'. \File::extension($filepath);
							\Storage::disk('payments')->put($filename, \Storage::disk('images-payments')->get($request->get('image_gallery')));
							if ( \Storage::disk('payments')->exists($filename) ) {
									$image = $filename;
									$delete = 1;
							}
					}

					if ( $delete && !empty($prevImage) && \Storage::disk('payments')->exists($prevImage) ) {
							\Storage::disk('payments')->delete($prevImage);
					}

					$rowPayment = new \stdClass();
					$rowPayment->id = null;
					$rowPayment->site_id = ($site_id) ? $site_id : (int)$siteId;
					$rowPayment->name = trim($name);
					$rowPayment->description = trim($description);
					$rowPayment->service_ids = $service_ids;
					$rowPayment->payment_page = trim($payment_page);
					$rowPayment->image = $image;
					$rowPayment->params = trim($params);
					$rowPayment->method = trim($method);
					$rowPayment->factor_type = $factor_type;
					$rowPayment->price = $price;
					$rowPayment->default = $default;
					$rowPayment->ordering = $ordering;
					$rowPayment->published = $published;
					$rowPayment->is_backend = $is_backend;

					if ( $default > 0 ) {
							$sql = "UPDATE `{$dbPrefix}payment` SET `default`='0' WHERE `site_id`='" . $siteId . "' AND `default`='1'";
							$db->update($sql);
					}

					if ( !empty($queryPayment) ) {
							$rowPayment->id = $queryPayment->id;
							unset($rowPayment->site_id);

							$results = \DB::table('payment')->where('id', $rowPayment->id)->update((array)$rowPayment);
							$results = $rowPayment->id;
					}
					else {
							$results = \DB::table('payment')->insertGetId((array)$rowPayment);
							$rowPayment->id = $results;
					}

					$success = true;
					$data['id'] = $rowPayment->id;
					$data['method'] = $rowPayment->method;
			}

			$data['success'] = $success;
			$data['errors'] = $errors;

	break;
	case 'destroy':
            if (!auth()->user()->hasPermission('admin.payments.destroy')) {
                return redirect_no_permission();
            }

		$id = (int)$etoPost['id'];

		$sql = "SELECT `id`, `image`
						FROM `{$dbPrefix}payment`
						WHERE `site_id`='". $siteId ."'
						AND `id`='". $id ."'
						LIMIT 1";

		$query = $db->select($sql);
		if (!empty($query[0])) {
			$query = $query[0];
		}

		if ( !empty($query) ) {
			if ( !empty($query->image) && \Storage::disk('payments')->exists($query->image) ) {
					\Storage::disk('payments')->delete($query->image);
			}

			$sql = "DELETE FROM `{$dbPrefix}payment` WHERE `id`='". $query->id ."' LIMIT 1";
			$results2 = $db->delete($sql);

			$data['success'] = true;
		}
		else {
			$data['message'][] = $gLanguage['API']['ERROR_NO_PAYMENT'];
		}

	break;
}
