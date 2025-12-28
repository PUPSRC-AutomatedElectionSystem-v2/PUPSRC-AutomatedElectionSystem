<form action="{{ route('org.api.v1.voters.import') }}" method="POST" enctype="multipart/form-data">
    <label for="votersFileInput">Import</label>
    <input type="file" name="votersFileList" id="votersFileInput" accept=".csv,.xlsx,.xls">

    <input type="submit" value="Upload">
</form>
