"use client";
import React, { useState, useCallback, JSX } from "react";
import {
  Box,
  Button,
  Checkbox,
  FormControl,
  InputLabel,
  MenuItem,
  Paper,
  Select,
  SelectChangeEvent,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  TextField,
  Typography,
} from "@mui/material";
import Swal from "sweetalert2";
import { fetchEmployees } from "@/components/attendance/fetch/GET";
import { submitBulkAttendance } from "@/components/attendance/fetch/POST";
import {
  AttendanceBulkFilter,
  AttendanceStatus,
  Employee,
} from "@/definitions/helpers";
export default function AttendanceBulkPage(): JSX.Element {
  const [filter, setFilter] = useState<AttendanceBulkFilter>({
      date: "",
      branch: "",
      department: "",
    }),
    [employees, setEmployees] = useState<Employee[]>([]),
    [attendanceMap, setAttendanceMap] = useState<
      Record<string, AttendanceStatus>
    >({}),
    [selectAll, setSelectAll] = useState<boolean>(false),
    handleFilterChange = useCallback(
      (e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) =>
        setFilter({ ...filter, [e.target.name]: e.target.value }),
      [filter]
    ),
    handleSelectChange = useCallback(
      (e: SelectChangeEvent, field: "branch" | "department") =>
        setFilter({ ...filter, [field]: e.target.value as string }),
      [filter]
    ),
    handleFilterSubmit = useCallback(
      async (e: React.FormEvent<HTMLFormElement>): Promise<void> => {
        e.preventDefault();
        const emps = await fetchEmployees(filter);
        setEmployees(emps);
        const newAttendance: Record<string, AttendanceStatus> = {};
        emps.forEach(emp => {
          newAttendance[emp.id] = { status: "", clock_in: "", clock_out: "" };
        });
        setAttendanceMap(newAttendance);
      },
      [filter]
    ),
    toggleSelectAll = useCallback(() => {
      const newSelectAll = !selectAll;
      setSelectAll(newSelectAll);
      const newAttendance = { ...attendanceMap };
      employees.forEach(emp => {
        newAttendance[emp.id] = {
          ...newAttendance[emp.id],
          status: newSelectAll ? "Present" : "",
        };
      });
      setAttendanceMap(newAttendance);
    }, [selectAll, employees, attendanceMap]),
    handleAttendanceToggle = useCallback((employeeId: string): void => {
      setAttendanceMap(prev => {
        const current = prev[employeeId]?.status === "Present" ? "" : "Present";
        return {
          ...prev,
          [employeeId]: { ...prev[employeeId], status: current },
        };
      });
    }, []),
    handleTimeChange = useCallback(
      (
        employeeId: string,
        field: "clock_in" | "clock_out",
        value: string
      ): void => {
        setAttendanceMap(prev => ({
          ...prev,
          [employeeId]: { ...prev[employeeId], [field]: value },
        }));
      },
      []
    ),
    handleSubmitAttendance = useCallback(async (): Promise<void> => {
      const result = await submitBulkAttendance({
        date: filter.date,
        branch: filter.branch,
        department: filter.department,
        attendance: attendanceMap,
      });
      if (result)
        Swal.fire({
          icon: "success",
          title: "Success",
          text: "Attendance updated successfully.",
        });
    }, [filter, attendanceMap]), // Dummy branch and department options; these can be replaced with dynamic data
    branchOptions = { branch1: "Branch 1", branch2: "Branch 2" },
    departmentOptions = { dept1: "Department 1", dept2: "Department 2" };
  return (
    <Box id='bulk-attendance-manager' className='p-4'>
      <Typography variant='h5' id='page-title' className='mb-4'>
        Manage Bulk Attendance
      </Typography>
      <form id='bulkattendance_filter' onSubmit={handleFilterSubmit}>
        <Box id='filter-box' className='flex flex-wrap justify-end gap-4 mb-4'>
          <TextField
            label='Date'
            type='date'
            name='date'
            value={filter.date}
            onChange={handleFilterChange}
            InputLabelProps={{ shrink: true }}
            required
            id='filter-date'
          />
          <FormControl required id='filter-branch'>
            <InputLabel id='branch-label'>Branch</InputLabel>
            <Select
              labelId='branch-label'
              value={filter.branch}
              onChange={e => handleSelectChange(e, "branch")}
              label='Branch'
            >
              {Object.entries(branchOptions).map(([key, value]) => (
                <MenuItem key={key} value={key} id={`branch-option-${key}`}>
                  {value}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <FormControl required id='filter-department'>
            <InputLabel id='department-label'>Department</InputLabel>
            <Select
              labelId='department-label'
              value={filter.department}
              onChange={e => handleSelectChange(e, "department")}
              label='Department'
            >
              {Object.entries(departmentOptions).map(([key, value]) => (
                <MenuItem key={key} value={key} id={`department-option-${key}`}>
                  {value}
                </MenuItem>
              ))}
            </Select>
          </FormControl>
          <Button
            variant='contained'
            color='primary'
            type='submit'
            id='filter-submit'
          >
            Apply
          </Button>
        </Box>
      </form>
      <TableContainer component={Paper} id='attendance-table-container'>
        <Table id='attendance-table'>
          <TableHead>
            <TableRow>
              <TableCell width='10%'>
                <Checkbox
                  checked={selectAll}
                  onChange={toggleSelectAll}
                  inputProps={{ "aria-label": "select all attendance" }}
                  id='present_all'
                />
                Attendance
              </TableCell>
              <TableCell>Employee Id</TableCell>
              <TableCell>Employee</TableCell>
              <TableCell>Branch</TableCell>
              <TableCell>Department</TableCell>
              <TableCell>Time In/Out</TableCell>
            </TableRow>
          </TableHead>
          <TableBody>
            {employees.map(emp => {
              const att = attendanceMap[emp.id] || {
                status: "",
                clock_in: "",
                clock_out: "",
              };
              return (
                <TableRow key={emp.id} id={`employee-row-${emp.id}`}>
                  <TableCell>
                    <input type='hidden' name='employee_id[]' value={emp.id} />
                    <Checkbox
                      checked={att.status === "Present"}
                      onChange={() => handleAttendanceToggle(emp.id)}
                      inputProps={{ "aria-label": `attendance ${emp.name}` }}
                      className='present'
                      id={`present${emp.id}`}
                    />
                  </TableCell>
                  <TableCell>
                    <a
                      href={`/employee/${emp.id}`}
                      id={`employee-link-${emp.id}`}
                    >
                      {emp.id}
                    </a>
                  </TableCell>
                  <TableCell>{emp.name}</TableCell>
                  <TableCell>{emp.branch?.name || ""}</TableCell>
                  <TableCell>{emp.department?.name || ""}</TableCell>
                  <TableCell>
                    {att.status === "Present" && (
                      <Box
                        id={`present_check_in-${emp.id}`}
                        className='flex space-x-2'
                      >
                        <TextField
                          type='time'
                          name={`in-${emp.id}`}
                          value={att.clock_in}
                          onChange={e =>
                            handleTimeChange(emp.id, "clock_in", e.target.value)
                          }
                          id={`time-in-${emp.id}`}
                        />
                        <TextField
                          type='time'
                          name={`out-${emp.id}`}
                          value={att.clock_out}
                          onChange={e =>
                            handleTimeChange(
                              emp.id,
                              "clock_out",
                              e.target.value
                            )
                          }
                          id={`time-out-${emp.id}`}
                        />
                      </Box>
                    )}
                  </TableCell>
                </TableRow>
              );
            })}
          </TableBody>
        </Table>
      </TableContainer>
      <Box id='attendance-submit-container' className='flex justify-end pt-4'>
        <input type='hidden' name='date' value={filter.date} />
        <input type='hidden' name='branch' value={filter.branch} />
        <input type='hidden' name='department' value={filter.department} />
        <Button
          variant='contained'
          color='primary'
          onClick={handleSubmitAttendance}
          id='submit-attendance-btn'
        >
          Update
        </Button>
      </Box>
    </Box>
  );
}
