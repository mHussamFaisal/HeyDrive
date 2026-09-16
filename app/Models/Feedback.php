<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Feedback extends Model
{
    protected $table = 'feedback';
    protected $fillable = [];
    protected $hidden = [];
    protected $guarded = [];

    protected $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    public function relation()
    {
        return $this->morphTo();
    }

    public function getName()
    {
        return ucfirst($this->name);
    }

    public function getRefNumberLink($params = [])
    {
        if (empty($this->ref_number)) {
            return '';
        }

        $bookings = \App\Models\BookingRoute::where('ref_number', $this->ref_number)->get();

        if ($bookings->count() == 1) {
            $link = route('admin.bookings.show', ['id' => $bookings->first()->id]);
        }
        else {
            $link = route('admin.bookings.index', ['search' => $this->ref_number]);
        }

        $class = '';
        $style = '';

        if (!empty($params)) {
            if (!empty($params['class'])) {
                $class = 'class="'. $params['class'] .'"';
            }

            if (!empty($params['style'])) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        return '<a href="'. $link .'" '. $class .' '. $style .'>'. $this->ref_number .'</a>';
    }

    public function getTypeLink($params = [])
    {
        $class = '';
        $style = '';

        if (!empty($params)) {
            if (!empty($params['class'])) {
                $class = 'class="'. $params['class'] .'"';
            }

            if (!empty($params['style'])) {
                $style = 'style="'. $params['style'] .'"';
            }
        }

        $name = \Lang::has('admin/feedback.types.'. $this->type) ? trans('admin/feedback.types.'. $this->type) : trans('admin/feedback.types.other');

        return '<a href="'. route('admin.feedback.index', ['type' => $this->type]) .'" '. $class .' '. $style .'>'. $name .'</a>';
    }

    public function getParams($type = 'none')
    {
        $params = json_decode($this->getOriginal('params'));

        if ($type == 'raw') {
            $value = (object)[
                //
            ];
        }
        else {
            $value = '';
        }

        return $value;
    }

    public function getStatus($type = 'none')
    {
        switch ($this->status) {
            case 'active':
                $name = trans('admin/feedback.statuses.active');
                $color = '#00a65a';
            break;
            default:
                $name = trans('admin/feedback.statuses.inactive');
                $color = '#dd4b39';
            break;
        }

        switch ($type) {
            case 'label':
                $value = '<span class="label" style="background:'. $color .';">'. $name .'</span>';
            break;
            case 'color':
                $value = '<span style="color:'. $color .';">'. $name .'</span>';
            break;
            default:
                $value = $name;
            break;
        }

        return $value;
    }

    public function getFiles($json = false)
    {
        $files = [];
        $id = $this->id;

        if (!empty($id)) {
            $query = \DB::table('file')
                ->where('file_relation_type', 'feedback')
                ->where('file_relation_id', $id)
                ->orderBy('file_id', 'asc')
                ->get();

            foreach($query as $v) {
                $files[] = (object)[
                    'id' => $v->file_id,
                    'name' => $v->file_name,
                    'file_path' => $v->file_path,
                    'path' => route('admin.feedback.download', $v->file_id)
                ];
            }
        }

        if ($json) {
            $files = json_encode($files);
        }

        return $files;
    }

    public function deleteFiles()
    {
        $id = $this->id;

        if ($id) {
            $query = \DB::table('file')
               ->where('file_relation_type', 'feedback')
               ->where('file_relation_id', $id)
               ->get();

            foreach($query as $v) {
                if (\Storage::disk('safe')->exists($v->file_path)) {
                    \Storage::disk('safe')->delete($v->file_path);
                }
                \DB::table('file')->where('file_id', $v->file_id)->delete();
            }
        }
    }
}
