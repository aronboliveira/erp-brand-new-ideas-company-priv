"use client";
import {
  useState,
  useEffect,
  useCallback,
  ChangeEvent,
  FormEvent,
} from "react";
import {
  AnnouncementFormData,
  Branch,
  Department,
  Employee,
} from "@/definitions/helpers";
import { AnnouncementFormProps } from "@/definitions/components";
import {
  postAnnouncement,
  postBranches,
  postDepartments,
  postEmployees,
} from "./fetch/POST";
export default function AnnouncementCreate({
  onCancel,
  onSubmitSuccess,
}: AnnouncementFormProps) {
  const [branches, setBranches] = useState<Branch[]>([]),
    [departments, setDepartments] = useState<Department[]>([]),
    [employees, setEmployees] = useState<Employee[]>([]),
    [form, setForm] = useState<AnnouncementFormData>({
      title: "",
      branch_id: "",
      department_id: "",
      employee_id: "",
      start_date: "",
      end_date: "",
      description: "",
    });
  useEffect(() => {
    postBranches(setBranches);
  }, []);
  const fetchDepartments = useCallback(
      (branchId: string) => postDepartments(branchId, setDepartments),
      [postDepartments]
    ),
    fetchEmployees = useCallback(
      (departmentId: string) => postEmployees(departmentId, setEmployees),
      [postEmployees]
    );
  useEffect(() => {
    form.branch_id && fetchDepartments(form.branch_id);
  }, [form.branch_id, fetchDepartments]);
  useEffect(() => {
    form.department_id && fetchEmployees(form.department_id);
  }, [form.department_id, fetchEmployees]);
  const handleChange = (
    e: ChangeEvent<HTMLInputElement | HTMLSelectElement | HTMLTextAreaElement>
  ) => setForm(prev => ({ ...prev, [e.target.name]: e.target.value }));
  const handleSubmit = useCallback(
    async (e: FormEvent) => {
      e.preventDefault();
      try {
        await postAnnouncement(form);
        onSubmitSuccess();
      } catch (err) {
        console.error(`Creating announcement failed: ${err}`);
      }
    },
    [postAnnouncement, onSubmitSuccess]
  );
  return (
    <form onSubmit={handleSubmit}>
      <div className='row g-3'>
        <div className='col-md-6'>
          <label className='form-label'>Announcement Title</label>
          <input
            className='form-control'
            name='title'
            onChange={handleChange}
            required
          />
        </div>
        <div className='col-md-6'>
          <label className='form-label'>Branch</label>
          <select
            className='form-select'
            name='branch_id'
            onChange={handleChange}
            required
          >
            <option value=''>Select Branch</option>
            <option value='0'>All Branches</option>
            {branches.map(({ id, name }) => (
              <option key={id} value={id}>
                {name}
              </option>
            ))}
          </select>
        </div>
        <div className='col-md-6'>
          <label className='form-label'>Department</label>
          <select
            className='form-select'
            name='department_id'
            onChange={handleChange}
          >
            <option value=''>Select Department</option>
            <option value='0'>All Departments</option>
            {departments.map(({ id, name }) => (
              <option key={id} value={id}>
                {name}
              </option>
            ))}
          </select>
        </div>
        <div className='col-md-6'>
          <label className='form-label'>Employee</label>
          <select
            className='form-select'
            name='employee_id'
            onChange={handleChange}
          >
            <option value=''>Select Employee</option>
            <option value='0'>All Employees</option>
            {employees.map(({ id, name }) => (
              <option key={id} value={id}>
                {name}
              </option>
            ))}
          </select>
        </div>
        <div className='col-md-6'>
          <label className='form-label'>Start Date</label>
          <input
            type='date'
            className='form-control'
            name='start_date'
            onChange={handleChange}
            required
          />
        </div>
        <div className='col-md-6'>
          <label className='form-label'>End Date</label>
          <input
            type='date'
            className='form-control'
            name='end_date'
            onChange={handleChange}
            required
          />
        </div>
        <div className='col-md-12'>
          <label className='form-label'>Announcement Description</label>
          <textarea
            className='form-control'
            name='description'
            rows={3}
            onChange={handleChange}
          />
        </div>
        <div className='col-12 text-end'>
          <button
            type='button'
            className='btn btn-light me-2'
            onClick={onCancel}
          >
            Cancel
          </button>
          <button type='submit' className='btn btn-primary'>
            Create
          </button>
        </div>
      </div>
    </form>
  );
}
