<?php

namespace App\Repositories\Focus\hrm;

use App\Models\Access\Permission\PermissionUser;
use App\Models\Access\Role\Role;
use App\Models\Access\User\UserProfile;
use App\Models\employee\RoleUser;
use App\Models\hrm\HrmMeta;
use DateTime;
use DB;
use App\Models\hrm\Hrm;
use App\Exceptions\GeneralException;
use App\Models\attendance\Attendance;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\Storage;
use App\Utils\MessageUtil;
use Illuminate\Support\Str;
use App\Repositories\Focus\general\RosemailerRepository;
use Illuminate\Validation\ValidationException;

/**
 * Class HrmRepository.
 */
class HrmRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */


    const MODEL = Hrm::class;
    protected $file_picture_path;
    protected $file_sign_path;
    protected $file_cv_path;
    protected $storage;
    protected $messageUtil;

    /**
     * Constructor.
     */
    public function __construct(MessageUtil $messageUtil)
    {
        $this->file_picture_path = 'img' . DIRECTORY_SEPARATOR . 'users' . DIRECTORY_SEPARATOR;
        $this->file_sign_path = 'img' . DIRECTORY_SEPARATOR . 'signs' . DIRECTORY_SEPARATOR;
        $this->file_cv_path = 'img' . DIRECTORY_SEPARATOR . 'cvs' . DIRECTORY_SEPARATOR;
        $this->storage = Storage::disk('public');
        $this->messageUtil = $messageUtil;
    }

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {
        $q = $this->query()->where('first_name', 'NOT LIKE', '%Admin%')->where('last_name', 'NOT LIKE', '%Admin%');

        if (request('rel_type') == 2 and request('rel_id')) {
            $q->whereHas('meta', function ($s) {
                return $s->where('department_id', '=', request('rel_id', 0));
            });
        }

        return $q->with(['monthlysalary'])->get(['id', 'email', 'picture', 'first_name', 'last_name', 'status', 'created_at']);
    }

    /**
     * Get Attendance Data
     */
    public function getForAttendanceDataTable()
    {
        $q = Attendance::query();

        $q->when(request('rel_id'), function ($q) {
            $q->where('user_id', request('rel_id'));
        });

        return $q->get();
    }


    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @return bool
     * @throws GeneralException
     */
    public function create(array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) 
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                if (isset($val['signature'])) 
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
                if (isset($val['cv'])) 
                    $input[$key]['cv'] = $this->uploadPicture($val['cv'], $this->file_cv_path);
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) 
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                if (isset($val['id_back'])) 
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
            }
        }

        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            // create hrm
            $username = random_username();
            $password = random_password();
            $input['employee'] = array_replace($input['employee'], [
                'username' => $username,
                'password' => (new DateTime('now'))->format('y') . '-' . 'Pa$$w0rd!',
                'created_by' => auth()->user()->id,
                'confirmed' => 1,
            ]);
            unset($input['employee']['role']);
            $hrm = Hrm::create($input['employee']);

            // create hrm meta
            $input['meta'] = array_replace($input['meta'], [
                'dob' => date_for_database($input['meta']['dob']),
                'employement_date' => date_for_database($input['meta']['employement_date']),
                'user_id' => $hrm->id,
            ]);
            if (!$input['meta']['is_cronical']) $input['meta']['specify'] = 'none';
            HrmMeta::create($input['meta']);

            // create user role and permissions
            RoleUser::create(['user_id' => $hrm->id, 'role_id' => $role_id]);
            if (isset($input['permission'])) $hrm->permissions()->attach($input['permission']);

            if ($hrm) {
                // $this->messageUtil->sendMessage($input['meta']['primary_contact'], $email_input['text']);
                $email_input = [
                    'text' => "Account Created Successfully. Username: {$username}, Email: {$input['employee']['email']}, Password: {$password}",
                    'subject' => 'Login Credentials',
                    'mail_to' => $input['employee']['email'],
                    'customer_name' => @$input['employee']['first_name'] . ' ' . @$input['employee']['last_name'],
                ];
                (new RosemailerRepository)->send($email_input['text'], $email_input);

                DB::commit();
                return $hrm;
            }
        }
    }

    /**
     * For updating the respective Model in storage
     *
     * @param Hrm $hrm
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(Hrm $hrm, array $input)
    {
        // dd($input);
        DB::beginTransaction();

        foreach ($input as $key => $val) {
            if ($key == 'employee') {
                if (isset($val['picture'])) {
                    if ($this->storage->exists($this->file_picture_path . $hrm->picture)) {
                        $this->storage->delete($this->file_picture_path . $hrm->picture);
                    }
                    $input[$key]['picture'] = $this->uploadPicture($val['picture'], $this->file_picture_path);
                }
                if (isset($val['signature'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->signature)) {
                        $this->storage->delete($this->file_sign_path . $hrm->signature);
                    }
                    $input[$key]['signature'] = $this->uploadPicture($val['signature'], $this->file_sign_path);
                }
                if (isset($val['cv'])) {
                    if ($this->storage->exists($this->file_cv_path . $hrm->cv)) {
                        $this->storage->delete($this->file_cv_path . $hrm->cv);
                    }
                    $input[$key]['cv'] = $this->uploadPicture($val['cv'], $this->file_cv_path);
                }
            }
            if ($key == 'meta') {
                if (isset($val['id_front'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_front)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_front);
                    }
                    $input[$key]['id_front'] = $this->uploadPicture($val['id_front'], $this->file_sign_path);
                }
                if (isset($val['id_back'])) {
                    if ($this->storage->exists($this->file_sign_path . $hrm->id_back)) {
                        $this->storage->delete($this->file_sign_path . $hrm->id_back);
                    }
                    $input[$key]['id_back'] = $this->uploadPicture($val['id_back'], $this->file_sign_path);
                }
                $input[$key]['dob'] = date_for_database($val['dob']);
                $input[$key]['employement_date'] = date_for_database($val['employement_date']);
            }
        }

        $role_id = $input['employee']['role'];
        $role = Role::find($role_id);
        if ($role && $role->status == 1) {
            // update hrm
            unset($input['employee']['role']);
            $hrm->update($input['employee']);

            // update hrm meta
            $hrm_meta = HrmMeta::where('user_id', $hrm->id)->first();
            if ($hrm_meta) $hrm_meta->update($input['meta']);

            // update role user and permissions
            $role_user = RoleUser::where('user_id', $hrm->id)->first();
            if ($role_user) $role_user->update(compact('role_id'));
            PermissionUser::where('user_id', $hrm->id)->delete();
            if (isset($input['permission'])) $hrm->permissions()->attach($input['permission']);
            
            DB::commit();
            return true;
        }
        
        

        // throw new GeneralException(trans('exceptions.backend.hrms.update_error'));
    }

    /**
     * For deleting the respective model from storage
     *
     * @param \App\Models\hrm\Hrm $hrm
     * @return bool
     * @throws GeneralException
     */
    public function delete(Hrm $hrm)
    {
        if (auth()->user()->id == $hrm->id) 
            throw ValidationException::withMessages(['Not allowed!']);

        DB::beginTransaction();

        $params = ['user_id' => $hrm->id];
        HrmMeta::where($params)->delete();
        RoleUser::where($params)->delete();
        UserProfile::where($params)->delete();
        if ($hrm->delete()) {
            DB::commit();
            return true;
        }
    }

    /*
    * Upload logo image
    */
    public function uploadPicture($logo, $path)
    {
        $image_name = time() . $logo->getClientOriginalName();
        $this->storage->put($path . $image_name, file_get_contents($logo->getRealPath()));
        return $image_name;
    }
}
