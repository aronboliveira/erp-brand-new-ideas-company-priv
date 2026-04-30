import React, { useState, memo, JSX, useCallback } from "react";
import {
  Box,
  Button,
  Dialog,
  DialogActions,
  DialogContent,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  SelectChangeEvent,
  TextField,
} from "@mui/material";
import Swal from "sweetalert2";
import { AttendanceEditProps } from "@/definitions/components";
import { EmployeeAttendance } from "@/definitions/helpers";
import { updateEmployeeAttendance } from "./fetch/PUT";
function AttendanceEdit({
  EmployeeAttendance,
  employees,
  onClose,
  onSuccess,
}: AttendanceEditProps): JSX.Element {
  const [form, setForm] = useState<EmployeeAttendance>(EmployeeAttendance),
    handleChange = (e: React.ChangeEvent<HTMLInputElement>) =>
      setForm({ ...form, [e.target.name]: e.target.value }),
    handleEmployeeChange = (e: SelectChangeEvent) =>
      setForm({ ...form, id: e.target.value as string }),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>) => {
        e.preventDefault();
        try {
          const updated = await updateEmployeeAttendance(form);
          if (updated) {
            Swal.fire({
              icon: "success",
              title: "Updated",
              text: "Attendance updated successfully",
            });
            onSuccess();
          }
        } catch (err: any) {
          console.error(`handleSubmit error: ${err?.message}`);
        }
      },
      [updateEmployeeAttendance, onSuccess]
    );
  return (
    <Dialog
      open
      onClose={onClose}
      fullWidth
      maxWidth='md'
      id='edit-attendance-modal'
    >
      <form onSubmit={handleSubmit} id='attendance-employee-edit-form'>
        <DialogContent id='modal-body'>
          <Box className='grid grid-cols-12 gap-4'>
            <FormControl
              fullWidth
              className='col-span-6'
              id='employee-form-control'
            >
              <InputLabel id='employee-label'>Employee</InputLabel>
              <Select
                labelId='employee-label'
                id='employee-select'
                value={form.id}
                label='Employee'
                onChange={handleEmployeeChange}
                className='select2'
              >
                {employees.map(emp => (
                  <MenuItem
                    key={emp.value}
                    value={emp.value}
                    id={`employee-option-${emp.value}`}
                  >
                    {emp.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <TextField
              fullWidth
              className='col-span-6'
              label='Date'
              name='date'
              type='date'
              InputLabelProps={{ shrink: true }}
              value={form.date}
              onChange={handleChange}
              id='date-input'
            />
            <TextField
              fullWidth
              className='col-span-6'
              label='Clock In'
              name='clock_in'
              type='time'
              value={form.clock_in}
              onChange={handleChange}
              id='clock-in-input'
            />
            <TextField
              fullWidth
              className='col-span-6'
              label='Clock Out'
              name='clock_out'
              type='time'
              value={form.clock_out}
              onChange={handleChange}
              id='clock-out-input'
            />
          </Box>
        </DialogContent>
        <DialogActions id='modal-footer'>
          <Button
            variant='outlined'
            type='button'
            onClick={onClose}
            id='cancel-btn'
          >
            Cancel
          </Button>
          <Button variant='contained' type='submit' id='submit-btn'>
            Update
          </Button>
        </DialogActions>
      </form>
    </Dialog>
  );
}
export default memo(AttendanceEdit);
