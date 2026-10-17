<?php

namespace ME\SchoolManagement\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;

/**
 * Many tables cascade on delete (deleting a class would also delete its
 * students, their fees, marks, ...). Deletes go through here so a record
 * that is still in use is refused with a readable reason instead.
 */
trait GuardsDeletes
{
    /**
     * @param array<string,int> $uses  label => number of records still using $model
     */
    protected function deleteUnlessUsed(Model $model, array $uses, string $title, string $success): RedirectResponse
    {
        $inUse = array_filter($uses, fn ($n) => $n > 0);
        if ($inUse) {
            $list = collect($inUse)->map(fn ($n, $label) => number_format($n) . ' ' . $label)->implode(', ');
            return back()->with('error', "Cannot delete \"{$title}\": it is used by {$list}. "
                . 'Remove those first, or mark it inactive instead.');
        }

        $this->logDelete($model, $title, fn () => $model->delete());

        return back()->with('success', $success);
    }

    /** Rows in $table whose $column equals $id (includes binned rows, which still hold the reference). */
    protected function uses(string $table, string $column, $id): int
    {
        return \Illuminate\Support\Facades\DB::table($table)->where($column, $id)->count();
    }

    /** Record a delete (with the deleted data) in the theme's activity log. */
    protected function logDelete(Model $model, string $title, callable $delete): mixed
    {
        if (function_exists('me_change_log')) {
            return me_change_log(class_basename($model) . " deleted: {$title}")->watch($model)->delete($delete);
        }
        return $delete();
    }
}
