import { useState, useEffect, JSX } from "react";
import axios from "axios";
import { useQuery } from "@tanstack/react-query";
import Link from "next/link";
import {
  Box,
  Typography,
  Button,
  FormControl,
  InputLabel,
  Select,
  MenuItem,
  Table,
  TableBody,
  TableCell,
  TableContainer,
  TableHead,
  TableRow,
  Paper,
  IconButton,
  SelectChangeEvent,
} from "@mui/material";
import VisibilityIcon from "@mui/icons-material/Visibility";
import EditIcon from "@mui/icons-material/Edit";
import DeleteIcon from "@mui/icons-material/Delete";
import { Appraisal } from "@/definitions/helpers";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
import AdminPage from "../admin/page";
export default function AppraisalPage(): JSX.Element {
  const [branch, setBranch] = useState<string>(""),
    [employeeOptions, setEmployeeOptions] = useState<
      Array<{ key: string; value: string }>
    >([]),
    {
      data: appraisals,
      error,
      isLoading,
    } = useQuery<Appraisal[]>({
      queryKey: ["appraisals"],
      queryFn: async () => {
        try {
          const res = await axios.get("/api/appraisals");
          return res?.data ?? [];
        } catch (err: any) {
          console.error(`fetchAppraisals error: ${err?.message}`);
          return [];
        }
      },
    }),
    getEmployee = async (branchId: string): Promise<void> => {
      try {
        const res = await axios.post("/api/branch/employee", {
          branch: branchId,
          _token: "CSRF_TOKEN",
        }); // TODO: Replace CSRF token logic;
        if (res?.data) {
          const options = Object.entries(res.data).map(([key, value]) => ({
            key,
            value: String(value),
          }));
          setEmployeeOptions([
            { key: "", value: "Select Employee" },
            ...options,
          ]);
        }
      } catch (err: any) {
        console.error(`getEmployee error: ${err?.message}`);
      }
    };
  useEffect(() => {
    getEmployee(branch);
  }, [branch]);
  const handleBranchChange = (
    e: React.ChangeEvent<{ value: unknown }> | SelectChangeEvent
  ): void => setBranch(e.target.value as string);
  if (isLoading) return <Typography>Loading...</Typography>;
  if (error) return <Typography>Error loading appraisals.</Typography>;
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AdminPage>
        <Box id='manage-appraisal-page'>
          <Box id='page-title' component='header'>
            <Typography variant='h4'>Manage Appraisal</Typography>
          </Box>
          <Box id='breadcrumb' component='nav'>
            <ol>
              <li id='breadcrumb-dashboard'>
                <Link href='/dashboard'>Dashboard</Link>
              </li>
              <li id='breadcrumb-appraisal'>Appraisal</li>
            </ol>
          </Box>
          <Box id='action-btn' sx={{ textAlign: "right", mb: 2 }}>
            <Button
              id='create-appraisal-btn'
              variant='contained'
              size='small'
              color='primary'
              onClick={() => {
                // TODO: Add navigation logic
              }}
            >
              Create New Appraisal
            </Button>
          </Box>
          <Box id='employee-section' sx={{ mb: 2 }}>
            <FormControl sx={{ mr: 2, minWidth: 120 }} size='small'>
              <InputLabel id='branch-select-label'>Branch</InputLabel>
              <Select
                labelId='branch-select-label'
                id='branch-select'
                value={branch}
                label='Branch'
                onChange={handleBranchChange}
              >
                <MenuItem value=''>
                  <em>Select Branch</em>
                </MenuItem>
                {/* TODO: Add branch options */}
              </Select>
            </FormControl>
            <FormControl sx={{ minWidth: 120 }} size='small'>
              <InputLabel id='employee-select-label'>Employee</InputLabel>
              <Select
                labelId='employee-select-label'
                id='employee-select'
                label='Employee'
              >
                {employeeOptions.map((opt, i) => (
                  <MenuItem key={i} value={opt.key}>
                    {opt.value}
                  </MenuItem>
                ))}
              </Select>
            </FormControl>
          </Box>
          <TableContainer component={Paper} id='appraisal-table-container'>
            <Table id='appraisal-table'>
              <TableHead>
                <TableRow>
                  <TableCell>Branch</TableCell>
                  <TableCell>Department</TableCell>
                  <TableCell>Designation</TableCell>
                  <TableCell>Employee</TableCell>
                  <TableCell>Target Rating</TableCell>
                  <TableCell>Overall Rating</TableCell>
                  <TableCell>Appraisal Date</TableCell>
                  <TableCell width='200px'>Action</TableCell>
                </TableRow>
              </TableHead>
              <TableBody id='table-body'>
                {appraisals?.map(appraisal => {
                  const targetRating = appraisal.targetRating ?? 0;
                  const overallRating = appraisal.overallRating ?? 0;
                  return (
                    <TableRow key={appraisal.id}>
                      <TableCell>{appraisal.branch?.name ?? ""}</TableCell>
                      <TableCell>
                        {appraisal.employee?.department?.name ?? ""}
                      </TableCell>
                      <TableCell>
                        {appraisal.employee?.designation?.name ?? ""}
                      </TableCell>
                      <TableCell>{appraisal.employee?.name ?? ""}</TableCell>
                      <TableCell>
                        {[1, 2, 3, 4, 5].map(i =>
                          targetRating < i ? (
                            isNaN(targetRating) ||
                            Math.round(targetRating) !== i ? (
                              <i
                                key={i}
                                className='fas fa-star'
                                role='img'
                                aria-label='star'
                              />
                            ) : (
                              <i
                                key={i}
                                className='text-warning fas fa-star-half-alt'
                                role='img'
                                aria-label='half star'
                              />
                            )
                          ) : (
                            <i
                              key={i}
                              className='text-warning fas fa-star'
                              role='img'
                              aria-label='star'
                            />
                          )
                        )}
                        <span className='theme-text-color'>{`(${targetRating.toFixed(
                          1
                        )})`}</span>
                      </TableCell>
                      <TableCell>
                        {[1, 2, 3, 4, 5].map(i =>
                          overallRating < i ? (
                            isNaN(overallRating) ||
                            Math.round(overallRating) !== i ? (
                              <i
                                key={i}
                                className='fas fa-star'
                                role='img'
                                aria-label='star'
                              />
                            ) : (
                              <i
                                key={i}
                                className='text-warning fas fa-star-half-alt'
                                role='img'
                                aria-label='half star'
                              />
                            )
                          ) : (
                            <i
                              key={i}
                              className='text-warning fas fa-star'
                              role='img'
                              aria-label='star'
                            />
                          )
                        )}
                        <span className='theme-text-color'>{`(${overallRating.toFixed(
                          1
                        )})`}</span>
                      </TableCell>
                      <TableCell>{appraisal.appraisal_date}</TableCell>
                      <TableCell>
                        <IconButton
                          id={`view-${appraisal.id}`}
                          onClick={() => {
                            // TODO: Add view logic
                          }}
                          color='info'
                          size='small'
                        >
                          <VisibilityIcon />
                        </IconButton>
                        <IconButton
                          id={`edit-${appraisal.id}`}
                          onClick={() => {
                            // TODO: Add edit logic
                          }}
                          color='primary'
                          size='small'
                        >
                          <EditIcon />
                        </IconButton>
                        <IconButton
                          id={`delete-${appraisal.id}`}
                          onClick={() => {
                            // TODO: Add delete logic
                          }}
                          color='error'
                          size='small'
                        >
                          <DeleteIcon />
                        </IconButton>
                      </TableCell>
                    </TableRow>
                  );
                })}
              </TableBody>
            </Table>
          </TableContainer>
        </Box>
      </AdminPage>
    </ErrorBoundary>
  );
}
