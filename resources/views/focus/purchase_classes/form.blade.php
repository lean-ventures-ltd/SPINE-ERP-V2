<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<div>

    <div class="row mb-2">
        <div class="col-12 col-lg-4">
            <label for="name" class="mt-2">Name</label>
            <input type="text" id="name" name="name" required class="form-control box-size mb-2">
        </div>

        <div class="col-8 col-lg-4">
            <label for="budget" class="mt-2">Budget</label>
            <input type="number" step="0.01" id="budget" name="budget" required class="form-control box-size mb-2">
        </div>

        <div class="col-12 col-lg-8">
            <label for="financial_year_id" >Financial Year</label>
            <select class="form-control box-size mb-2" id="financial_year_id" name="financial_year_id" required>

                <option value=""> Select a Financial Year </option>

                @foreach ($financialYears as $fY)
                    <option value="{{ $fY['id'] }}">
                        {{ $fY['name'] }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-lg-8">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="col-8 col-lg-8 tinyinput" cols="30" rows="10"></textarea>
        </div>
    </div>

</div>



