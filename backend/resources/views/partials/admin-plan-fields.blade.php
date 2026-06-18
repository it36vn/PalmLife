@php
    $periods = [
        'lifetime' => $tr['period_lifetime'],
        'week' => $tr['period_week'],
        'month' => $tr['period_month'],
        'unlimited' => $tr['period_unlimited'],
    ];
    $types = [
        'free' => $tr['store_free'],
        'subscription' => $tr['store_subscription'],
        'non_consumable' => $tr['store_non_consumable'],
    ];
@endphp

<label>
    {{ $tr['plan_code'] }}
    <input class="input" name="code" value="{{ old('code', $plan?->code) }}" required>
</label>
<label>
    {{ $tr['name_vi'] }}
    <input class="input" name="name_vi" value="{{ old('name_vi', $plan?->name_vi) }}" required>
</label>
<label>
    {{ $tr['name_en'] }}
    <input class="input" name="name_en" value="{{ old('name_en', $plan?->name_en) }}" required>
</label>
<label>
    {{ $tr['price_vnd'] }}
    <input class="input" name="price_vnd" type="number" min="0" value="{{ old('price_vnd', $plan?->price_vnd ?? 0) }}" required>
</label>
<label>
    {{ $tr['quota'] }}
    <input class="input" name="quota_limit" type="number" min="0" value="{{ old('quota_limit', $plan?->quota_limit) }}" placeholder="{{ $tr['quota_placeholder'] }}">
</label>
<label>
    {{ $tr['quota_period'] }}
    <select class="select" name="quota_period" required>
        @foreach ($periods as $value => $label)
            <option value="{{ $value }}" @selected(old('quota_period', $plan?->quota_period ?? 'month') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
<label>
    {{ $tr['store_type'] }}
    <select class="select" name="store_product_type" required>
        @foreach ($types as $value => $label)
            <option value="{{ $value }}" @selected(old('store_product_type', $plan?->store_product_type ?? 'subscription') === $value)>{{ $label }}</option>
        @endforeach
    </select>
</label>
<label>
    {{ $tr['default_plan'] }}
    <select class="select" name="is_default">
        <option value="0" @selected(! old('is_default', $plan?->is_default ?? false))>{{ $tr['no'] }}</option>
        <option value="1" @selected((bool) old('is_default', $plan?->is_default ?? false))>{{ $tr['yes'] }}</option>
    </select>
</label>
<label class="wide">
    {{ $tr['apple_product_id'] }}
    <input class="input" name="apple_product_id" value="{{ old('apple_product_id', $plan?->apple_product_id) }}" required>
</label>
<label class="wide">
    {{ $tr['google_product_id'] }}
    <input class="input" name="google_product_id" value="{{ old('google_product_id', $plan?->google_product_id) }}" required>
</label>
<label class="wide">
    {{ $tr['description_vi'] }}
    <textarea name="description_vi" required>{{ old('description_vi', $plan?->description_vi) }}</textarea>
</label>
<label class="wide">
    {{ $tr['description_en'] }}
    <textarea name="description_en" required>{{ old('description_en', $plan?->description_en) }}</textarea>
</label>
