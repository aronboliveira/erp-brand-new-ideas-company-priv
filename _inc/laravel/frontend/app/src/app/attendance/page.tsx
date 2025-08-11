"use client";
import {
  Button,
  Card,
  CardContent,
  FormControl,
  FormControlLabel,
  InputLabel,
  MenuItem,
  Radio,
  RadioGroup,
  Select,
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableRow,
  TextField,
  Tooltip,
} from "@mui/material";
import { useState, useEffect, useCallback } from "react";
import Swal from "sweetalert2";
import { getAttendanceList } from "@/api/attendance";
import { useQuery } from "@tanstack/react-query";
import { format } from "date-fns";
export default function AttendancePage() {
  const [filterType, setFilterType] = useState<"monthly" | "daily">("monthly"),
    [month, setMonth] = useState(format(new Date(), "yyyy-MM")),
    [date, setDate] = useState(format(new Date(), "yyyy-MM-dd")),
    [branch, setBranch] = useState(""),
    [department, setDepartment] = useState(""),
    handleFilterChange = useCallback(
      (e: React.ChangeEvent<HTMLInputElement>) => {
        setFilterType(e.target.value as "monthly" | "daily");
      },
      []
    ),
    { data: attendanceData, error } = useQuery({
      queryKey: ["attendance", filterType, month, date, branch, department],
      queryFn: () =>
        getAttendanceList({ filterType, month, date, branch, department }),
    });
  useEffect(() => {
    error && Swal.fire("Error", error.message, "error");
  }, [error]);
  return (
    <>
      <Card className='mb-4'>
        <CardContent>
          <form id='EmployeeAttendance_filter'>
            <div className='flex flex-wrap gap-4 items-center'>
              <FormControl>
                <RadioGroup
                  row
                  value={filterType}
                  onChange={handleFilterChange}
                >
                  <FormControlLabel
                    value='monthly'
                    control={<Radio />}
                    label='Monthly'
                  />
                  <FormControlLabel
                    value='daily'
                    control={<Radio />}
                    label='Daily'
                  />
                </RadioGroup>
              </FormControl>{" "}
              {filterType === "monthly" ? (
                <TextField
                  type='month'
                  label='Month'
                  value={month}
                  onChange={e => setMonth(e.target.value)}
                />
              ) : (
                <TextField
                  type='date'
                  label='Date'
                  value={date}
                  onChange={e => setDate(e.target.value)}
                />
              )}{" "}
              <FormControl>
                <InputLabel>Branch</InputLabel>
                <Select
                  value={branch}
                  label='Branch'
                  onChange={e => setBranch(e.target.value)}
                  className='min-w-[120px]'
                >
                  {/* Populate from API */}
                  <MenuItem value=''>Select Branch</MenuItem>
                </Select>
              </FormControl>{" "}
              <FormControl>
                <InputLabel>Department</InputLabel>
                <Select
                  value={department}
                  label='Department'
                  onChange={e => setDepartment(e.target.value)}
                  className='min-w-[120px]'
                >
                  {/* Populate from API */}
                  <MenuItem value=''>Select Department</MenuItem>
                </Select>
              </FormControl>{" "}
              <Tooltip title='Apply'>
                <Button type='submit' variant='contained'>
                  Search
                </Button>
              </Tooltip>
              <Tooltip title='Reset'>
                <Button variant='contained' color='error' href='/attendance'>
                  Reset
                </Button>
              </Tooltip>
              <Tooltip title='Import CSV'>
                <Button variant='contained' href='/import/attendance'>
                  Import
                </Button>
              </Tooltip>
            </div>
          </form>
        </CardContent>
      </Card>{" "}
      <Card>
        <CardContent>
          <Table>
            <TableHead>
              <TableRow>
                <TableCell>Employee</TableCell>
                <TableCell>Date</TableCell>
                <TableCell>Status</TableCell>
                <TableCell>Clock In</TableCell>
                <TableCell>Clock Out</TableCell>
                <TableCell>Late</TableCell>
                <TableCell>Early Leaving</TableCell>
                <TableCell>Overtime</TableCell>
                <TableCell>Action</TableCell>
              </TableRow>
            </TableHead>
            <TableBody>
              {attendanceData?.map(attendance => (
                <TableRow key={attendance.id}>
                  <TableCell>{attendance.employee?.name ?? "-"}</TableCell>
                  <TableCell>
                    {format(new Date(attendance.date), "yyyy-MM-dd")}
                  </TableCell>
                  <TableCell>{attendance.status}</TableCell>
                  <TableCell>
                    {attendance.clock_in !== "00:00:00"
                      ? attendance.clock_in
                      : "00:00"}
                  </TableCell>
                  <TableCell>
                    {attendance.clock_out !== "00:00:00"
                      ? attendance.clock_out
                      : "00:00"}
                  </TableCell>
                  <TableCell>{attendance.late}</TableCell>
                  <TableCell>{attendance.early_leaving}</TableCell>
                  <TableCell>{attendance.overtime}</TableCell>
                  <TableCell>
                    <Tooltip title='Edit'>
                      <Button
                        variant='contained'
                        size='small'
                        href={`/attendance/edit/${attendance.id}`}
                      >
                        Edit
                      </Button>
                    </Tooltip>
                    <Tooltip title='Delete'>
                      <Button
                        variant='contained'
                        color='error'
                        size='small'
                        onClick={() => {
                          Swal.fire({
                            title: "Are You Sure?",
                            text: "This action cannot be undone.",
                            icon: "warning",
                            showCancelButton: true,
                            confirmButtonText: "Yes, delete it!",
                          }).then(result => {
                            if (result.isConfirmed) {
                              // implement deletion logic
                            }
                          });
                        }}
                      >
                        Delete
                      </Button>
                    </Tooltip>
                  </TableCell>
                </TableRow>
              ))}
            </TableBody>
          </Table>
        </CardContent>
      </Card>
    </>
  );
}
