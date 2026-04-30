import React, { useEffect, useState, JSX } from "react";
import { useRouter } from "next/navigation";
import {
  Box,
  Button,
  Breadcrumbs,
  Link as MuiLink,
  Paper,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Typography,
  IconButton,
  Tooltip,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
} from "@mui/material";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import AddIcon from "@mui/icons-material/Add";
import { format } from "date-fns";
import { Announcement, Option } from "@/definitions/helpers";
import Swal from "sweetalert2";
import { ManageAnnouncementProps } from "@/definitions/components";
import {
  fetchDepartment,
  fetchEmployee,
} from "../../components/announcements/fetch/POST";
import { deleteAnnouncement } from "../../components/announcements/fetch/DELETE";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
import AdminPage from "../admin/page";
export default function AnnouncementPage({
  announcements,
  branchOptions,
  canCreate,
  canEdit,
  canDelete,
}: ManageAnnouncementProps): JSX.Element {
  const router = useRouter(),
    [branch, setBranch] = useState(""),
    [department, setDepartment] = useState(""),
    [employee, setEmployee] = useState(""),
    [departmentOptions, setDepartmentOptions] = useState<Option[]>([]),
    [employeeOptions, setEmployeeOptions] = useState<Option[]>([]);
  useEffect(() => {
    if (branch)
      fetchDepartment(branch).then((opts: Option[]) =>
        setDepartmentOptions([
          { value: "", label: "Select Department" },
          { value: "0", label: "All Department" },
          ...opts,
        ])
      );
  }, [branch]);
  useEffect(() => {
    if (department)
      fetchEmployee(department).then((opts: Option[]) =>
        setEmployeeOptions([
          { value: "", label: "Select Employee" },
          { value: "0", label: "All Employee" },
          ...opts,
        ])
      );
  }, [department]);
  const handleDelete = async (id: string): Promise<void> => {
    try {
      const result = await Swal.fire({
        icon: "warning",
        title: "Are You Sure?",
        text: "This action cannot be undone. Do you want to continue?",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
      });
      if (result.isConfirmed) {
        const success = await deleteAnnouncement(id);
        if (success)
          await Swal.fire({
            icon: "success",
            title: "Deleted",
            text: "Announcement deleted successfully",
          });
        // TODO: update state to remove deleted announcement
      }
    } catch (err: any) {
      console.error(`handleDelete error: ${err?.message}`);
    }
  };
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AdminPage>
        <Box sx={{ p: 2 }} id='manage-announcement-page'>
          <Typography variant='h4' sx={{ mb: 2 }} id='page-title'>
            Manage Announcement
          </Typography>
          <Breadcrumbs aria-label='breadcrumb' sx={{ mb: 2 }} id='breadcrumb'>
            <MuiLink
              underline='hover'
              color='inherit'
              href='/dashboard'
              id='dashboard-link'
            >
              Dashboard
            </MuiLink>
            <Typography color='text.primary' id='announcement-crumb'>
              Announcement
            </Typography>
          </Breadcrumbs>
          {canCreate ? (
            <Box sx={{ mb: 2, textAlign: "right" }} id='create-button-box'>
              <Button
                variant='contained'
                startIcon={<AddIcon />}
                onClick={() => router.push("/announcement/create")}
                id='create-button'
              >
                Create New Announcement
              </Button>
            </Box>
          ) : null}
          <Box sx={{ mb: 2, display: "flex", gap: 2 }} id='filter-box'>
            <FormControl size='small' id='branch-formcontrol'>
              <InputLabel id='branch-label'>Branch</InputLabel>
              <Select
                labelId='branch-label'
                id='branch-select'
                value={branch}
                label='Branch'
                onChange={e => setBranch(e.target.value)}
              >
                {branchOptions.map((opt: Option) => (
                  <MenuItem
                    key={opt.value}
                    value={opt.value}
                    id={`branch-option-${opt.value}`}
                  >
                    {opt.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl size='small' id='department-formcontrol'>
              <InputLabel id='department-label'>Department</InputLabel>
              <Select
                labelId='department-label'
                id='department-select'
                value={department}
                label='Department'
                onChange={e => setDepartment(e.target.value)}
              >
                {departmentOptions.map(opt => (
                  <MenuItem
                    key={opt.value}
                    value={opt.value}
                    id={`department-option-${opt.value}`}
                  >
                    {opt.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
            <FormControl size='small' id='employee-formcontrol'>
              <InputLabel id='employee-label'>Employee</InputLabel>
              <Select
                labelId='employee-label'
                id='employee-select'
                value={employee}
                label='Employee'
                onChange={e => setEmployee(e.target.value)}
              >
                {employeeOptions.map(opt => (
                  <MenuItem
                    key={opt.value}
                    value={opt.value}
                    id={`employee-option-${opt.value}`}
                  >
                    {opt.label}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>
          <TableContainer component={Paper} id='announcement-table-container'>
            <Table id='announcement-table'>
              <TableHead>
                <TableRow id='table-head-row'>
                  <TableCell id='table-cell-title'>Title</TableCell>
                  <TableCell id='table-cell-start'>Start Date</TableCell>
                  <TableCell id='table-cell-end'>End Date</TableCell>
                  <TableCell id='table-cell-description'>Description</TableCell>
                  {canEdit || canDelete ? (
                    <TableCell id='table-cell-action'>Action</TableCell>
                  ) : null}
                </TableRow>
              </TableHead>
              <TableBody id='table-body'>
                {announcements.map((ann: Announcement) => (
                  <TableRow key={ann.id} id={`table-row-${ann.id}`}>
                    <TableCell id={`title-${ann.id}`}>{ann.title}</TableCell>
                    <TableCell id={`start-${ann.id}`}>
                      {format(new Date(ann.start_date), "MM/dd/yyyy")}
                    </TableCell>
                    <TableCell id={`end-${ann.id}`}>
                      {format(new Date(ann.end_date), "MM/dd/yyyy")}
                    </TableCell>
                    <TableCell id={`desc-${ann.id}`}>
                      {ann.description}
                    </TableCell>
                    {canEdit || canDelete ? (
                      <TableCell id={`action-${ann.id}`}>
                        {canEdit ? (
                          <Tooltip title='Edit' id={`tooltip-edit-${ann.id}`}>
                            <IconButton
                              onClick={() =>
                                router.push(`/announcement/${ann.id}/edit`)
                              }
                              id={`edit-btn-${ann.id}`}
                            >
                              <EditIcon />
                            </IconButton>
                          </Tooltip>
                        ) : null}
                        {canDelete ? (
                          <Tooltip
                            title='Delete'
                            id={`tooltip-delete-${ann.id}`}
                          >
                            <IconButton
                              onClick={() => handleDelete(ann.id)}
                              id={`delete-btn-${ann.id}`}
                            >
                              <DeleteIcon />
                            </IconButton>
                          </Tooltip>
                        ) : null}
                      </TableCell>
                    ) : null}
                  </TableRow>
                ))}
              </TableBody>
            </Table>
          </TableContainer>
        </Box>
      </AdminPage>
    </ErrorBoundary>
  );
}
