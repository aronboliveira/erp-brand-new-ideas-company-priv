import React, { JSX, useState } from "react";
import { useRouter } from "next/router";
import {
  Box,
  Button,
  Grid,
  TextField,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  SelectChangeEvent,
} from "@mui/material";
import {
  AppraisalFormData,
  BranchOption,
  EmployeeOption,
} from "@/definitions/helpers";
import { getEmployeesByBranch, getAppraisal } from "./fetch/GET";
import { postAppraisal } from "./fetch/POST";
import Swal from "sweetalert2";
export default function AppraisalCreateForm({
  branches,
}: {
  branches: BranchOption[];
}): JSX.Element {
  const router = useRouter(),
    [formData, setFormData] = useState<AppraisalFormData>({
      branchId: "",
      employeeId: "",
      appraisal_date: "",
      remark: "",
    }),
    [employees, setEmployees] = useState<EmployeeOption[]>([]),
    [stares, setStares] = useState(""),
    handleChange = (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => setFormData({ ...formData, [e.target.name]: e.target.value }),
    handleBranchChange = async (
      e:
        | React.ChangeEvent<{ name?: string; value: unknown }>
        | SelectChangeEvent
    ) => {
      try {
        const branch = branches.find(b => b.id === formData.branchId);
        if (!branch) throw new TypeError(`Branch name could not be found!`);
        setFormData({
          ...formData,
          branchId: branch.id,
          employeeId: formData.employeeId || "",
        });
        const empOpts = await getEmployeesByBranch(branch.id);
        setEmployees(empOpts);
      } catch (e) {
        Swal.fire({
          icon: "error",
          title: "An error has occured!",
          text: "Failed to recognize branch!",
        });
        console.error(`Error : ${(e as Error).name} — ${(e as Error).message}`);
      }
    },
    handleEmployeeChange = async (
      e:
        | React.ChangeEvent<{ name?: string; value: unknown }>
        | SelectChangeEvent
    ) => {
      try {
        const employee = employees.find(ep => ep.id === e.target.value);
        if (!employee) throw new TypeError(`Employee could not be found!`);
        setFormData({ ...formData, employeeId: employee.id });
        const html = await getAppraisal(employee.id);
        setStares(html);
      } catch (error) {
        Swal.fire({
          icon: "error",
          title: "An error has occured!",
          text: "Failed to recognize employee!",
        });
        console.error(
          `Error : ${(error as Error).name} — ${(error as Error).message}`
        );
      }
    },
    handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      await postAppraisal(formData);
      router.push("/appraisal");
    };
  return (
    <Box
      component='form'
      id='appraisal-create-form'
      onSubmit={handleSubmit}
      sx={{ p: 2 }}
    >
      <Box sx={{ mb: 2 }}>
        <Grid container spacing={2}>
          <Grid item xs={12}>
            <FormControl fullWidth required>
              <InputLabel id='branch-label'>Branch*</InputLabel>
              <Select
                labelId='branch-label'
                id='branch'
                name='branch'
                value={formData.branchId}
                label='Branch*'
                onChange={handleBranchChange}
              >
                <MenuItem value='' disabled>
                  Select Branch
                </MenuItem>
                {branches.map(b => (
                  <MenuItem key={b.id} value={b.id}>
                    {b.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={6}>
            <FormControl fullWidth required>
              <InputLabel id='employee-label'>Employee*</InputLabel>
              <Select
                labelId='employee-label'
                id='employee'
                name='employee'
                value={formData.employeeId}
                label='Employee*'
                onChange={handleEmployeeChange}
              >
                <MenuItem value='' disabled>
                  Select Employee
                </MenuItem>
                {employees.map(emp => (
                  <MenuItem key={emp.id} value={emp.id}>
                    {emp.name}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Grid>
          <Grid item xs={12} sm={6}>
            <TextField
              fullWidth
              required
              id='appraisal_date'
              name='appraisal_date'
              label='Select Month*'
              type='month'
              value={formData.appraisal_date}
              onChange={handleChange}
              InputLabelProps={{ shrink: true }}
            />
          </Grid>
          <Grid item xs={12}>
            <TextField
              fullWidth
              multiline
              rows={3}
              id='remark'
              name='remark'
              label='Remarks'
              placeholder='Enter remark'
              value={formData.remark}
              onChange={handleChange}
            />
          </Grid>
          <Grid item xs={12}>
            <Box id='stares' dangerouslySetInnerHTML={{ __html: stares }} />
          </Grid>
        </Grid>
      </Box>
      <Box sx={{ display: "flex", justifyContent: "flex-end", gap: 1 }}>
        <Button variant='outlined' onClick={() => router.back()}>
          Cancel
        </Button>
        <Button variant='contained' type='submit'>
          Create
        </Button>
      </Box>
    </Box>
  );
}
