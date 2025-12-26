@props([
    'countyName' => 'county',
    'cityName' => 'city',
    'countyValue' => '',
    'cityValue' => '',
    'countyId' => 'county',
    'cityId' => 'city',
    'required' => false,
    'countyLabel' => 'County',
    'cityLabel' => 'City'
])

<div class="ny-location-selector">
    <div class="form-group">
        <label for="{{ $countyId }}">{{ $countyLabel }}</label>
        <select 
            name="{{ $countyName }}" 
            id="{{ $countyId }}" 
            class="form-control county-select"
            {{ $required ? 'required' : '' }}
            onchange="updateCities(this.value, '{{ $cityId }}')"
        >
            <option value="">Select County</option>
            @foreach(ny_county_options() as $value => $label)
                <option value="{{ $value }}" {{ $countyValue === $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group">
        <label for="{{ $cityId }}">{{ $cityLabel }}</label>
        <select 
            name="{{ $cityName }}" 
            id="{{ $cityId }}" 
            class="form-control city-select"
            {{ $required ? 'required' : '' }}
        >
            <option value="">Select City</option>
            @if($countyValue && $cityValue)
                @foreach(ny_city_options($countyValue) as $value => $label)
                    <option value="{{ $value }}" {{ $cityValue === $value ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            @endif
        </select>
    </div>
</div>

<script>
function updateCities(county, citySelectId) {
    const citySelect = document.getElementById(citySelectId);
    citySelect.innerHTML = '<option value="">Select City</option>';
    
    if (county) {
        fetch(`/api/ny-cities/${encodeURIComponent(county)}`)
            .then(response => response.json())
            .then(cities => {
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city;
                    option.textContent = city;
                    citySelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error loading cities:', error));
    }
}
</script>