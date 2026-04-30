import React, { JSX, useState } from "react";
import { useQuery, useQueryClient } from "@tanstack/react-query";
import Swal from "sweetalert2";
import Link from "next/link";
import {
  Box,
  Grid,
  Card,
  CardContent,
  Table,
  TableHead,
  TableRow,
  TableCell,
  TableBody,
  Paper,
  Button,
  Breadcrumbs,
  Typography,
} from "@mui/material";
import AllowanceOptionModal from "../../components/allowanceOption/Create";
import AllowanceOptionUpdateModal from "../../components/allowanceOption/Edit";
import HRMSetup from "../../app/hrm-setup/page";
import { AllowanceOption } from "../../definitions/helpers";
import { getAllowanceOptions } from "../../components/allowanceOption/fetch/GET";
import AdminPage from "../admin/page";
import { ErrorBoundary } from "../../../node_modules/react-error-boundary/dist";
export const metadata = {
  title: "Manage Allowance Option",
  description: "Manage your allowance options",
};
export default function AllowanceOptionsPage(): JSX.Element {
  const queryClient = useQueryClient(),
    [createModalOpen, setCreateModalOpen] = useState(false),
    [editModalOpen, setEditModalOpen] = useState(false),
    [selectedOption, setSelectedOption] = useState<AllowanceOption | null>(
      null
    ),
    canCreate = true,
    canEdit = true,
    canDelete = true,
    { data, isLoading, error } = useQuery<AllowanceOption[]>({
      queryKey: ["allowanceOptions"],
      queryFn: async () => getAllowanceOptions(),
    }),
    handleDelete = async (option: AllowanceOption): Promise<void> => {
      try {
        const result = await Swal.fire({
          icon: "warning",
          title: "Are you sure?",
          text: "This action cannot be undone.",
          showCancelButton: true,
          confirmButtonText: "Yes, delete it",
        });
        if (result.isConfirmed) {
          // TODO: Add delete logic;
          queryClient.invalidateQueries({ queryKey: ["allowanceOptions"] });
        }
      } catch (err: any) {
        console.error(`handleDelete error: ${err?.message}`);
      }
    };
  return (
    <ErrorBoundary FallbackComponent={() => <></>}>
      <AdminPage>
        <Box id='allowance-options-page'>
          <Box my={4}>
            <Typography variant='h4' gutterBottom>
              Manage Allowance Option
            </Typography>
            <Breadcrumbs aria-label='breadcrumb'>
              <Link
                href='/dashboard'
                style={{ textDecoration: "none", color: "inherit" }}
              >
                Dashboard
              </Link>
              <Typography color='text.primary'>Allowance Option</Typography>
            </Breadcrumbs>
          </Box>
          <Box mb={2} display='flex' justifyContent='flex-end'>
            {canCreate ? (
              <Button
                variant='contained'
                color='primary'
                size='small'
                onClick={() => setCreateModalOpen(true)}
              >
                Create New Allowance Option
              </Button>
            ) : null}
          </Box>
          <Grid container spacing={2}>
            <Grid item xs={12} md={3}>
              <HRMSetup />
            </Grid>
            <Grid item xs={12} md={9}>
              <Card>
                <CardContent>
                  {isLoading ? (
                    <Typography>Loading...</Typography>
                  ) : error ? (
                    <Typography color='error'>Error loading data.</Typography>
                  ) : (
                    <Paper>
                      <Table>
                        <TableHead>
                          <TableRow>
                            <TableCell>Allowance Option</TableCell>
                            <TableCell width='200px'>Action</TableCell>
                          </TableRow>
                        </TableHead>
                        <TableBody>
                          {data && data.length > 0 ? (
                            data.map(option => (
                              <TableRow key={option.id}>
                                <TableCell>{option.name}</TableCell>
                                <TableCell>
                                  {canEdit ? (
                                    <Button
                                      variant='contained'
                                      color='primary'
                                      size='small'
                                      onClick={() => {
                                        setSelectedOption(option);
                                        setEditModalOpen(true);
                                      }}
                                      style={{ marginRight: 8 }}
                                    >
                                      Edit
                                    </Button>
                                  ) : null}
                                  {canDelete ? (
                                    <Button
                                      variant='contained'
                                      color='secondary'
                                      size='small'
                                      onClick={() => handleDelete(option)}
                                    >
                                      Delete
                                    </Button>
                                  ) : null}
                                </TableCell>
                              </TableRow>
                            ))
                          ) : (
                            <TableRow>
                              <TableCell colSpan={2}>
                                No Allowance Options Found.
                              </TableCell>
                            </TableRow>
                          )}
                        </TableBody>
                      </Table>
                    </Paper>
                  )}
                </CardContent>
              </Card>
            </Grid>
          </Grid>
          {createModalOpen ? (
            <AllowanceOptionModal
              open={createModalOpen}
              onClose={() => setCreateModalOpen(false)}
              onSubmitSuccess={() =>
                queryClient.invalidateQueries({
                  queryKey: ["allowanceOptions"],
                })
              }
            />
          ) : null}
          {editModalOpen && selectedOption ? (
            <AllowanceOptionUpdateModal
              open={editModalOpen}
              onClose={() => {
                setEditModalOpen(false);
                setSelectedOption(null);
              }}
              allowanceOption={selectedOption}
              onSubmitSuccess={() =>
                queryClient.invalidateQueries({
                  queryKey: ["allowanceOptions"],
                })
              }
            />
          ) : null}
        </Box>
      </AdminPage>
    </ErrorBoundary>
  );
}
