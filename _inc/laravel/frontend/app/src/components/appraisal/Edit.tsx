import React, { useState, useEffect, JSX, useCallback } from "react";
import { useRouter } from "next/router";
import {
  Box,
  Button,
  FormControl,
  InputLabel,
  MenuItem,
  Select,
  TextField,
  Grid,
  SelectChangeEvent,
} from "@mui/material";
import {
  BranchOption,
  Appraisal,
  AppraisalFormData,
  EmployeeOption,
} from "@/definitions/helpers";
import {
  fetchEmployeesByBranch,
  fetchEmpByStar,
  fetchEmpByStar1,
} from "./fetch/POST";
import { updateAppraisal } from "./fetch/PUT";
export default function AppraisalEditForm({
  appraisal,
  branches,
}: {
  appraisal: Appraisal;
  branches: BranchOption[];
}): JSX.Element {
  const router = useRouter(),
    [formData, setFormData] = useState<AppraisalFormData>({
      branchId: appraisal.branch?.id.toString() || "",
      employeeId: appraisal.employee?.id.toString() || "",
      appraisal_date: appraisal.appraisal_date,
      remark: appraisal.remark,
    }),
    [employees, setEmployees] = useState<EmployeeOption[]>([]),
    [stares, setStares] = useState(""),
    handleChange = (
      e: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>
    ) => setFormData({ ...formData, [e.target.name]: e.target.value }),
    handleBranchChange = useCallback(
      async (
        e:
          | React.ChangeEvent<{ name?: string; value: unknown }>
          | SelectChangeEvent
      ) => {
        const branchId = e.target.value as string;
        setFormData({
          ...formData,
          branchId,
          employeeId: formData.employeeId || "",
        });
        const empOpts = await fetchEmployeesByBranch(branchId);
        setEmployees(empOpts);
      },
      [setEmployees, setFormData, fetchEmployeesByBranch]
    ),
    handleEmployeeChange = useCallback(
      async (
        e:
          | React.ChangeEvent<{ name?: string; value: unknown }>
          | SelectChangeEvent
      ) => {
        const employeeId = e.target.value as string;
        setFormData({ ...formData, employeeId });
        const html = await fetchEmpByStar(employeeId);
        setStares(html);
      },
      [fetchEmpByStar, setFormData, setStares]
    ),
    handleSubmit = async (e: React.FormEvent) => {
      e.preventDefault();
      await updateAppraisal(appraisal.id, formData);
      router.push("/appraisal");
    };
  useEffect(() => {
    async function loadEmployees() {
      const empOpts = await fetchEmployeesByBranch(
        appraisal.branch?.id.toString() ?? "NULL"
      );
      setEmployees(empOpts);
    }
    async function loadStares() {
      const html = await fetchEmpByStar1(
        appraisal.employee.id.toString(),
        appraisal.id
      );
      setStares(html);
    }
    loadEmployees();
    loadStares();
  }, [appraisal.branch, appraisal.employee, appraisal.id]);
  return (
    <Box
      component='form'
      id='appraisal-edit-form'
      onSubmit={handleSubmit}
      sx={{ p: 2 }}
    >
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
            value={formData.appraisal_date}
            onChange={handleChange}
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
            value={formData.remark}
            onChange={handleChange}
          />
        </Grid>
        <Grid item xs={12}>
          <Box id='stares' dangerouslySetInnerHTML={{ __html: stares }} />
        </Grid>
      </Grid>
      <Box sx={{ mt: 2, display: "flex", justifyContent: "flex-end", gap: 1 }}>
        <Button variant='outlined' onClick={() => router.back()}>
          Cancel
        </Button>
        <Button variant='contained' type='submit'>
          Update
        </Button>
      </Box>
    </Box>
  );
}
