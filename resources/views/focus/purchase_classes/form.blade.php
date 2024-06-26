<head>
    <script src="https://cdn.tiny.cloud/1/ewcb9ttdxkr6mv3uyc8ueykuqz06aja4t3e7wuqyfqfwq17z/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
</head>

<div>

    <div class="row mb-2">
        <div class="col-8 col-lg-4">
            <label for="name" class="mt-2">Name</label>
            <input type="text" id="name" name="name" required class="form-control box-size mb-2">
        </div>

        <div class="col-8 col-lg-4">
            <label for="budget" class="mt-2">Budget</label>
            <input type="number" step="0.01" id="budget" name="budget" required class="form-control box-size mb-2">
        </div>

        <div class="col-12 col-lg-8">
            <label for="description">Description</label>
            <textarea name="description" id="description" class="col-8 col-lg-8 tinyinput" cols="30" rows="10"></textarea>
        </div>
    </div>

    <div class="row my-2">

        <div class="col-4">
            <label for="from_date">Start Date</label>
            <input type="text" id="start_date" name="start_date" required placeholder="Start From..." class="datepicker form-control box-size mb-2">
        </div>

        <div class="col-4">
            <label for="to_date">End Date</label>
            <input type="text" id="end_date" name="end_date" required placeholder="End On..." class="datepicker form-control box-size mb-2">
        </div>

    </div>

</div>



