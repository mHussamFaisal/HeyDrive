<?php

namespace App\Traits\Filters;

use Illuminate\Support\Arr;

trait Feedback
{
    private $config;
    private $start = 0;
    private $count = 500;
    private $order = [];
    private $all = false;

    public function getFeedbackFilters($config)
    {
        return $config;
    }

    public function parseFeedbackFilters($filters, $name)
    {
        $key = $name;
        $data = [ $key => [
                'name' => $name,
                'type' => 'custom',
                'data' => []
            ]
        ];

        if (!empty($filters['status'])) {
            $data[$key]['data']['status'] = $filters['status'];
        }
        if (!empty($filters['type'])) {
            $data[$key]['data']['type'] = $filters['type'];
        }

        if (!empty($filters['feedback_created_from'])) {
            $data[$key]['data']['feedback_created_from'] = $filters['feedback_created_from'];
        }

        if (!empty($filters['feedback_created_to'])) {
            $data[$key]['data']['feedback_created_to'] = $filters['feedback_created_to'];
        }

        return $data;
    }

    public function getFeedbackData($config)
    {
        $feedback = \App\Models\Feedback::query();

        if (!empty($config['type'])) {
            $feedback->whereIn('type', $config['type']);
        }

        if (!empty($config['status'])) {
            $feedback->whereIn('status', $config['status']);
        }

        if (!empty($config['feedback_created_from']) && !empty($config['feedback_created_to'])) {
            $feedback->whereBetween('created_at', [$config['feedback_created_from'], $config['feedback_created_to']]);
        }
        elseif (!empty($config['profile_created_from'])) {
            $feedback->where('.created_at', '>=', $config['feedback_created_from']);
        }
        elseif (!empty($config['profile_created_to'])) {
            $feedback->where('created_at', '<=', $config['feedback_created_to']);
        }

        $data = $feedback->get();

        return $data;
    }

    public function getFeedbackFile($data, $columns, $format, $folder)
    {
        $headers = [];

        foreach($columns as $column=>$toExport) {
            if ($toExport) {
                $headers[$column] = trans('export.column.feedback.' . $column);
            }
        }

        if (!empty($data)) {
            foreach ($data->chunk($this->limitPerFile) as $chunkId => $chunk) {
                $cells = [$headers];

                foreach ($chunk as $feedback) {
                    $cell = [];

                    foreach ($headers as $hK => $hV) {
                        if ($hK == 'additional_files') {
                            $val = [];
                            $files = $feedback->getFiles();

                            if (count($files)) {
                                foreach ($files as $file) {
                                    if (\Storage::disk('safe')->exists($file->file_path)) {
                                        if (!\File::exists($folder . DIRECTORY_SEPARATOR . 'feedback')) {
                                            \File::makeDirectory($folder . DIRECTORY_SEPARATOR . 'feedback', 0755, true);
                                        }
                                        $val[] = $file->file_path;
                                        @copy(disk_path('safe', $file->file_path), $folder . DIRECTORY_SEPARATOR . 'feedback' . DIRECTORY_SEPARATOR . $file->file_path);
                                    }
                                }
                            }

                            $val = implode(' | ', $val);
                        } else {
                            $val = Arr::get($feedback, $hK);

                            if ($val instanceof \Carbon\Carbon) {
                                $val = format_date_time($val);
                            } elseif ($hK == 'type') {
                                $val = trans('admin/feedback.types.' . $val);
                            } elseif ($hK == 'status') {
                                $val = trans('export.fields.published.' . $val);
                            }
                        }

                        $val = trim(strip_tags($val));
                        $val = str_replace(array("\r\n", "\r", "\n"), ' ', $val);
                        $cell[$hK] = $val ? $val : '';
                    }

                    if (!empty($cell)) {
                        $cells[] = $cell;
                    }
                }

                $this->setFilePerChunkSection('Feedback', $chunkId, $format, $cells);
            }
        }
    }
}
