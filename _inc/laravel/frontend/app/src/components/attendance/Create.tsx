"use client";
import React, { JSX, useCallback, useState } from "react";
import {
  Box,
  Button,
  DialogActions,
  DialogContent,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
} from "@mui/material";
import { createEmployeeAttendance } from "./fetch/POST";
import { AttendanceCreateProps } from "@/definitions/components";
export default function AttendanceCreate({
  employees,
  onSuccess,
}: AttendanceCreateProps): JSX.Element {
  const [employeeId, setEmployeeId] = useState(""),
    [date, setDate] = useState(""),
    [clockIn, setClockIn] = useState(""),
    [clockOut, setClockOut] = useState(""),
    handleSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        const data = {
          employee_id: employeeId,
          date,
          clock_in: clockIn,
          clock_out: clockOut,
        };
        await createEmployeeAttendance(data, onSuccess);
      },
      [createEmployeeAttendance]
    );
  return (
    <form onSubmit={handleSubmit} id='attendance-employee-form'>
      <DialogContent id='modal-body'>
        <Box className='grid grid-cols-1 gap-4' id='form-fields'>
          <FormControl fullWidth id='employee-form-control'>
            <InputLabel id='employee-label'>Employee</InputLabel>
            <Select
              labelId='employee-label'
              id='employee-select'
              value={employeeId}
              label='Employee'
              onChange={e => setEmployeeId(e.target.value)}
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
            id='date-input'
            label='Date'
            type='text'
            value={date}
            onChange={e => setDate(e.target.value)}
            className='datepicker'
          />
          <TextField
            fullWidth
            id='clock-in-input'
            label='Clock In'
            type='time'
            value={clockIn}
            onChange={e => setClockIn(e.target.value)}
          />
          <TextField
            fullWidth
            id='clock-out-input'
            label='Clock Out'
            type='time'
            value={clockOut}
            onChange={e => setClockOut(e.target.value)}
          />
        </Box>
      </DialogContent>
      <DialogActions id='modal-footer'>
        <Button
          type='button'
          variant='outlined'
          onClick={() => {}}
          id='cancel-btn'
        >
          Cancel
        </Button>
        <Button type='submit' variant='contained' id='submit-btn'>
          Create
        </Button>
      </DialogActions>
    </form>
  );
}
