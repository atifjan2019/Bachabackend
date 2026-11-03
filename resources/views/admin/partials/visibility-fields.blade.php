{{--
    Status switch + sort order for the create/edit forms.
    $model: the Category or Product being edited (null on create).
    $what: "category" / "product", for the hint copy.
--}}
@php($isActive = old('is_active', $model ? $model->is_active : true))
<div class="form-group">
    <div class="form-check form-switch">
        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ $isActive ? 'checked' : '' }}>
        <label class="form-check-label" for="is_active">Active (shown on the website)</label>
    </div>
    <div class="form-hint">
        @if($what === 'category')
            Inactive categories are hidden from the shop, along with their subcategories and every product in them.
        @else
            Inactive products are hidden from the shop and search, but stay here and on past orders.
        @endif
    </div>
</div>
<div class="form-group">
    <label class="form-label">Sort Order</label>
    <input type="number" name="sort_order" class="form-control" min="0" max="999999" step="1" value="{{ old('sort_order', $model?->sort_order) }}" placeholder="Leave blank for default">
    <div class="form-hint">Lower numbers show first on the website. Blank {{ $what === 'category' ? 'categories list after the numbered ones, oldest first' : 'products list after the numbered ones, newest first' }}.</div>
</div>
